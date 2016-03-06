<?php
/**
 * Contains the functions for the forums
 * 
 * @package			Boardsolution
 * @subpackage	src
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Some functions for the forums
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ForumUtils extends FWS_UtilBase
{
	/**
	 * Builds all childforums of the given parent-id (0 = all forums)
	 *
	 * @param int $parent_id the parent-id
	 */
	public static function get_forum_list($parent_id)
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();

		if($input->get_var(BS_URL_LOC,'get',FWS_Input::STRING) == 'clapforum' &&
			 ($id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID)) != null)
			$functions->clap_forum($id);
	
		$start_layer = $parent_id == 0 ? 1 : $forums->get_node($parent_id)->get_layer() + 2;
		$num = 0;
		$cookie = $input->get_var(BS_COOKIE_PREFIX.'hidden_forums','cookie',FWS_Input::STRING);
		if($cookie == null)
			$cookie = $input->set_var(BS_COOKIE_PREFIX.'hidden_forums','cookie','');
	
		$hidden_forums = ($cookie != '') ? explode(',',$cookie) : array();
		$sub_nodes = $forums->get_sub_nodes($parent_id);
	
		// determine the number of visible forums
		$forum_num = count($sub_nodes);
		$real_num = $forum_num;
		for($i = 0;$i < $forum_num;$i++)
		{
			if($cfg['hide_denied_forums'] == 1 &&
				!$auth->has_access_to_intern_forum($sub_nodes[$i]->get_id()))
				$real_num--;
		}
	
		$clap_forum = false;
	
		// are forums available?
		if($real_num == 0)
		{
			// just show the message if we are displaying all forums (not just sub-forums)
			if($parent_id == 0)
				$msgs->add_notice($locale->lang('no_forums_available'));
		}
		else
		{
			$tpl->set_template('inc_forums.htm');
			
			$tpl->add_variables(array(
				'title' => ($parent_id == 0) ? $locale->lang('forums') : $locale->lang('subdirs'),
				'enable_moderators' => $cfg['enable_moderators'],
			));
	
			$images = array(
				'dot' => $user->get_theme_item_path('images/forums/path_dot.gif'),
				'middle' => $user->get_theme_item_path('images/forums/path_middle.gif')
			);
			
			$nodes = array();
			$fn = 0;
	
			$clapurl = BS_URL::get_mod_url('forums');
			$clapurl->set(BS_URL_LOC,'clapforum');
			
			$catinfo = array();
			$post_order = BS_PostingUtils::get_posts_order();
			$next_display_layer = -1;
			$sub_cats = array();
			for($i = 0;$i < $forum_num;$i++)
			{
				$node = $sub_nodes[$i];
				$daten = $node->get_data();
				/* @var $node FWS_Tree_Node */
				/* @var $daten BS_Forums_NodeData */
				$forum_id = $daten->get_id();
				$forum_type_cats = $daten->get_forum_type() == 'contains_cats';
				$clap_forum = $forum_type_cats;
				if($node->get_layer() <= $next_display_layer)
					$next_display_layer = -1;
	
				// do we want to display this forum?
				if($next_display_layer == -1)
				{
					if(!$daten->get_display_subforums())
						$next_display_layer = $node->get_layer();
	
					// skip this forum if the user is not allowed to view it
					if($cfg['hide_denied_forums'] == 1 &&
						 !$auth->has_access_to_intern_forum($forum_id))
						continue;
	
					if(!isset($sub_cats[$daten->get_parent_id()]))
						$sub_cats[$daten->get_parent_id()] = 1;
					else
						$sub_cats[$daten->get_parent_id()]++;
	
					// hide this category?
					if(in_array($forum_id,$hidden_forums))
					{
						$display_rubrik = 'none';
						$css_class = 'fa fa-plus fa-2x bs_plus_minus';
					}
					else
					{
						$display_rubrik = 'block';
						$css_class = 'fa fa-minus fa-2x bs_plus_minus';
					}
					
					$close_clap_forum = array();
					if(count($catinfo) > 0)
					{
						if((!$clap_forum || $catinfo[count($catinfo) - 1][1] != $node->get_id()) &&
								$node->get_layer() <= $catinfo[count($catinfo) - 1][0])
						{
								while(count($catinfo) > 0 && $node->get_layer() <= $catinfo[count($catinfo) - 1][0])
								{
									$close_clap_forum[] = 0;
									array_pop($catinfo);
								}
						}
					}
					
					// build the "forum-path"
					$pimages = array();
					if($node->get_layer() > 0)
					{
						$path_images = self::get_path_images($node,$sub_cats,$images,$start_layer);
						for($b = 0;$b < count($path_images);$b++)
						{
							$pimages[] = array(
								'image' => $path_images[$b]
							);
						}
					}
	
					$forum_url = BS_URL::build_topics_url($forum_id,1);
					
					$is_unread = false;
					if($forum_type_cats)
					{
						if(!$daten->get_display_subforums())
						{
							$clap_forum = false;
						}
						
						$clapurl->set(BS_URL_FID,$parent_id);
						$clapurl->set(BS_URL_ID,$forum_id);
						$nodes[$fn] = array(
							'contains_forums' => true,
							'forum_id' => $forum_id,
							'css_class' => $css_class,
							'display_rubrik' => $display_rubrik,
							'clap_forum' => $clap_forum,
							'clap_forum_url' => $clapurl->to_url(),
							'cookie_prefix' => BS_COOKIE_PREFIX
						);
					}
					else
					{
						$num++;
						$lp_data = array();
						$lp_data['tposts'] = $daten->get_lastpost_topicposts();
						$lp_data['post_time'] = $daten->get_lastpost_time();
						$lp_data['lastpost_id'] = $daten->get_lastpost_id();
						$lp_data['username'] = $daten->get_lastpost_username();
						$lp_data['threadname'] = $daten->get_lastpost_topicname();
						$lp_data['id'] = $daten->get_id();
						$lp_data['post_user'] = $daten->get_lastpost_userid();
						$lp_data['threadid'] = $daten->get_lastpost_topicid();
						$lp_data['post_an_user'] = $daten->get_lastpost_an_user();
						$lp_data['user_group'] = $daten->get_lastpost_usergroups();
						$lp_data['avatar'] = $daten->get_lastpost_avatar();
	
						if(!$daten->get_display_subforums())
						{
							$info = self::_get_subforum_info($daten->get_id());
							$thread_count = $daten->get_threads() + $info['threads'];
							$post_count = $daten->get_posts() + $info['posts'];
							if($info['lastpost'][0] > $daten->get_lastpost_time())
							{
								$lp_data['post_time'] = $info['lastpost'][0];
								$lp_data['lastpost_id'] = $info['lastpost'][1];
								$lp_data['username'] = $info['lastpost'][2];
								$lp_data['threadname'] = $info['lastpost'][3];
								$lp_data['id'] = $info['lastpost'][4];
								$lp_data['post_user'] = $info['lastpost'][5];
								$lp_data['threadid'] = $info['lastpost'][6];
								$lp_data['post_an_user'] = $info['lastpost'][7];
								$lp_data['tposts'] = $info['lastpost'][8];
								$lp_data['user_group'] = $info['lastpost'][9];
								$lp_data['avatar'] = $info['lastpost'][10];
							}
							$sub_forums = $info['sub_forums'];
						}
						else
						{
							$thread_count = $daten->get_threads();
							$post_count = $daten->get_posts();
							$sub_forums = "";
						}
	
						if(trim($daten->get_description()) != '')
							$beschr_ins = $daten->get_description();
						else
							$beschr_ins = '&nbsp;';
	
						$nodes[$fn] = array(
							'contains_forums' => false,
							'clap_forum' => $clap_forum,
							'mods_ins' => $auth->get_forum_mods($forum_id),
							'beschr_ins' => $beschr_ins,
							'lastpost' => self::_get_forum_lastpost($lp_data,$post_order),
							'alreadyread' => self::_get_forum_image($forum_id,$is_unread),
							'thread_count' => $thread_count,
							'post_count' => $post_count,
							'sub_forums' => $sub_forums
						);
					}
					
					$nodes[$fn]['is_unread'] = $is_unread;
					$nodes[$fn]['close_clap_forum'] = $close_clap_forum;
					$nodes[$fn]['forum_name_ins'] = $daten->get_name();
					$nodes[$fn]['forum_url'] = $forum_url;
					$nodes[$fn]['path_images'] = $pimages;
					
					if($clap_forum)
						array_push($catinfo, array($node->get_layer(), $node->get_id()));
					$fn++;
				}
			}
			
			$tpl->add_variable_ref('forums',$nodes);
			$tpl->add_variables(array(
				'clap_forum_bottom' => $catinfo,
				'forum_cookie' => $input->get_var(
					BS_COOKIE_PREFIX.'hidden_forums','cookie',FWS_Input::STRING
				)
			));
			
			$tpl->restore_template();
		}
	}
	
	/**
	 * collects all denied forums
	 *
	 * @param boolean $include_categories do you want to include categories?
	 * @return array an numeric array with the ids of the denied forums
	 */
	public static function get_denied_forums($include_categories = true)
	{
		$user = FWS_Props::get()->user();
		$forums = FWS_Props::get()->forums();

		if($user->is_admin() && !$include_categories)
			return array();
		
		$denied = array();
		$intern_access = self::get_intern_forum_permissions();
		foreach($forums->get_all_nodes() as $forum)
		{
			$data = $forum->get_data();
			$fid = $data->get_id();
			
			// categories don't contain topics, so its useless to search them
			if($include_categories && $data->get_forum_type() == 'contains_cats')
			{
				$denied[] = $fid;
				continue;
			}
	
			// admins have always permission
			if($user->is_admin())
				continue;
	
			// is it an intern forum?
			if(!$forums->is_intern_forum($fid))
				continue;
	
			if(isset($intern_access[$fid]) && $intern_access[$fid])
				continue;
	
			$denied[] = $fid;
		}
	
		return $denied;
	}
	
	/**
	 * collects the permissions for all intern forums
	 *
	 * @return array an associative array of the form:
	 * 	<code>
	 * 		array(<fid> => <accessAllowed>)
	 * 	</code>
	 */
	public static function get_intern_forum_permissions()
	{
		$user = FWS_Props::get()->user();
		$cache = FWS_Props::get()->cache();

		$result = array();
		$all_groups = $user->get_all_user_groups();
		foreach($cache->get_cache('intern') as $data)
		{
			if(!isset($result[$data['fid']]) || !$result[$data['fid']])
			{
				if($data['access_type'] == 'group')
					$result[$data['fid']] = in_array($data['access_value'],$all_groups);
				else
					$result[$data['fid']] = $user->get_user_id() == $data['access_value'];
			}
		}
	
		return $result;
	}
	
	/**
	 * generates the path to a forum and returns it
	 *
	 * @param int $rid the id of the forum
	 * @param boolean $start_with_raquo if you enable this the path will start with &amp;raquo;
	 * @return string the result
	 */
	public static function get_forum_path($rid = 0,$start_with_raquo = true)
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$id = ($rid != 0) ? $rid : $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$res = '';
		if($id != null)
		{
			$path = $forums->get_path($id);
			$len = count($path);
			for($i = $len - 1;$i >= 0;$i--)
			{
				if($i < $len - 1 || $start_with_raquo)
					$res .= ' &raquo; ';
				$res .= '<a href="'.BS_URL::build_topics_url($path[$i][1],1).'"';
	
				list($named,$namec) = FWS_StringHelper::get_limited_string(
					$path[$i][0],BS_MAX_FORUM_TITLE_LENGTH
				);
				if($namec != '')
					$res .= ' title="'.$namec.'">'.$named;
				else
					$res .= '>'.$named;
	
				$res .= '</a>';
			}
		}
	
		return $res;
	}
	
	/**
	 * Creates the options for the forum-combobox.
	 * Note that the combobox will be multiple if the name ends with "[]"
	 *
	 * @param string $name the name of the combobox.
	 * @param int|array $select the id of the selected forum
	 * @param int $disabled_forum the id of the forum to disable
	 * @param boolean $disable_categories do you want to disable categories?
	 * @param boolean $add_all_forums_option do you want to add an "all"-option?
	 * @return string the options of the combobox
	 */
	public static function get_recursive_forum_combo($name,$select,$disabled_forum,
		$disable_categories = true,$add_all_forums_option = false)
	{
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$forums = FWS_Props::get()->forums();

		$denied = self::get_denied_forums(false);
		$multiple = FWS_String::substr($name,-2,2) == '[]';
		
		if($multiple)
			$result = '<select name="'.$name.'" multiple="multiple" size="10">'."\n";
		else
			$result = '<select name="'.$name.'">'."\n";
		
		if($add_all_forums_option)
		{
			$selected = ($multiple && $select != null && in_array(0,$select)) || $select === 0;
			$result .= '	<option value="0"'.($selected ? ' selected="selected"' : '').'>&ndash; ';
			$result .= $locale->lang('all').' '.$locale->lang('forums').' &ndash;</option>'."\n";
		}
		
		$nodes = $forums->get_all_nodes();
		$len = count($nodes);
		for($i = 0;$i < $len;$i++)
		{
			$node = $nodes[$i];
			$fdata = $node->get_data();
			$fid = $fdata->get_id();
			
			// is the forum denied?
			if(in_array($fid,$denied))
			{
				if($cfg['hide_denied_forums'] == 1)
					continue;
				
				$disabled = ' disabled="disabled" style="color: #AAAAAA;"';
			}
			
			// select the forum?
			$selected = '';
			if($multiple && $select != null && in_array($fid,$select))
				$selected = ' selected="selected"';
			else if($select == $fid)
				$selected = ' selected="selected"';
	
			// disable the forum?
			if($fdata->get_forum_type() == 'contains_cats' && $disable_categories)
				$disabled = ' disabled="disabled" style="font-weight: bold; color: #AAAAAA;"';
			else if($fid == $disabled_forum)
				$disabled = ' disabled="disabled" style="color: #AAAAAA;"';
			else
				$disabled = '';
			
			// print forum
			$result .= '	<option value="'.$fid.'"'.$selected.$disabled.'>';
			for($a = 0;$a < $node->get_layer();$a++)
				$result .= ' --';
			
			$result .= ' '.$fdata->get_name().'</option>'."\n";
		}
		
		$result .= '</select>'."\n";
	
		return $result;
	}
	
	/**
	 * returns the path for the given forum
	 *
	 * @param FWS_Tree_Node $node the node of the forum
	 * @param array $sub_cats an associative array of the form:
	 * 	<code>array(<parent_id> => <sub_id>)</code>
	 * @param array $images an array with the images: <code>array('dot' => ...,'middle' => ...)</code>
	 * @param int $start_layer the start-layer
	 * @return array an numeric array with the path-images.
	 * 	each array-entry represents one "path-layer" beginning at the front
	 */
	public static function get_path_images($node,$sub_cats,$images,$start_layer = 0)
	{
		$forums = FWS_Props::get()->forums();

		$path_img = array();
		$layer = $node->get_layer();
		if($layer > 0)
		{
			$data = $node->get_data();
			$forum_type_cats = $data->get_forum_type() == 'contains_cats';
			$top = ($forum_type_cats) ? 12 : 9;
			$p_id = $data->get_parent_id();
			for($a = $start_layer;$a <= $layer;$a++)
			{
				$image = '';
				$bigger = !isset($sub_cats[$p_id]) || $forums->get_child_count($p_id) > $sub_cats[$p_id];
				if($a == 1 || $bigger)
				{
					if($bigger && $a == 1)
					{
						$image = '<img src="'.$images['dot'].'" height="'.$top.'" width="100%" alt="" /><br />';
						$image .= '<img src="'.$images['middle'].'" height="1" width="100%" alt="" /><br />';
						$image .= '<img src="'.$images['dot'].'" style="height: ';
						$image .= ($forum_type_cats ? '50' : '100').'%;" width="100%" alt="" />';
					}
					else if($bigger)
						$image = '<img src="'.$images['dot'].'" style="height: 100%;" width="100%" alt="" />';
					else
					{
						$image = '<img src="'.$images['dot'].'" height="'.$top.'" width="100%" alt="" /><br />';
						$image .= '<img width="100%" height="1" src="'.$images['middle'].'" alt="" />';
					}
				}
	
				array_unshift($path_img,$image);
				if($p_id > 0)
					$p_id = $forums->get_parent_id($p_id);
			}
		}
	
		return $path_img;
	}
	
	/**
	 * builds the subforum-info for the given parent-id
	 *
	 * @param int $parent_id the parent-id
	 * @return array an array of the form:
	 * 	<code>
	 * 		array(
	 * 			"threads" => ...,
	 * 			"posts" => ...,
	 * 			"lastpost" => ...,
	 * 			"sub_forums" => ...
	 *		)
	 * 	</code>
	 */
	private static function _get_subforum_info($parent_id)
	{
		$auth = FWS_Props::get()->auth();
		$forums = FWS_Props::get()->forums();

		$thread_num = 0;
		$post_num = 0;
		$sub_forums = "";
		$sub_forums_count = 1;
		$lastpost = array(0,0,"","",0,0,0,0,0);
		if($forums->has_childs($parent_id))
		{
			$nodes = $forums->get_sub_nodes($parent_id);
			for($i = 0;$i < count($nodes);$i++)
			{
				$node = $nodes[$i];
				$daten = $node->get_data();
				$fid = $daten->get_id();
				
				if($auth->has_access_to_intern_forum($fid))
				{
					if($node->get_layer() == 2 && $sub_forums_count <= BS_FORUM_SMALL_SUBDIR_DISPLAY)
					{
						$murl = BS_URL::build_topics_url($fid);
						$sub_forums .= "<a href=\"".$murl."\">".$daten->get_name()."</a>, ";
						$sub_forums_count++;
					}
					else if($sub_forums_count == BS_FORUM_SMALL_SUBDIR_DISPLAY + 1)
					{
						$sub_forums .= " ..., ";
						$sub_forums_count++;
					}
	
					if($daten->get_lastpost_id() != 0 && $daten->get_lastpost_time() > $lastpost[0])
					{
						$lastpost = array(
							$daten->get_lastpost_time(),
							$daten->get_lastpost_id(),
							$daten->get_lastpost_username(),
							$daten->get_lastpost_topicname(),
							$fid,
							$daten->get_lastpost_userid(),
							$daten->get_lastpost_topicid(),
							$daten->get_lastpost_an_user(),
							$daten->get_lastpost_topicposts(),
							$daten->get_lastpost_usergroups(),
							$daten->get_lastpost_avatar()
						);
					}
					
					$thread_num += $daten->get_threads();
					$post_num += $daten->get_posts();
				}
			}
		}
	
		if($sub_forums != '')
			$sub_forums = FWS_String::substr($sub_forums,0,FWS_String::strlen($sub_forums) - 2);
			
		return array(
			"threads" => $thread_num,
			"posts" => $post_num,
			"lastpost" => $lastpost,
			"sub_forums" => $sub_forums
		);
	}
	
	/**
	 * builds the lastpost-informations for the given data
	 *
	 * @param array $data the forum-data
	 * @param string $post_order the post-order: ASC or DESC
	 * @return array|bool the lastpost-informations or false if there is no last post
	 */
	private static function _get_forum_lastpost($data,$post_order)
	{
		if(!isset($data['tposts']))
			$data['tposts'] = 0;
	
		if($data['lastpost_id'] == 0)
			return false;
	
		$pages = BS_PostingUtils::get_post_pages($data['tposts'] + 1);
		list($tnamed,$tnamec) = BS_TopicUtils::get_displayed_name($data['threadname'],
			BS_MAX_TOPIC_LENGTH_LAST_POST);
	
		// generate url
		$site = 1;
		if($post_order == 'ASC' && $pages > 1)
			$site = $pages;
		$topic_url = BS_URL::build_posts_url($data['id'],$data['threadid'],1);
		if($site > 1)
			$lastpost_url = BS_URL::build_posts_url($data['id'],$data['threadid'],$site);
		else
			$lastpost_url = $topic_url;
	
		// determine username
		if($data['post_user'] != 0)
		{
			$user_name = BS_UserUtils::get_link(
				$data['post_user'],$data['username'],$data['user_group']
			);
		}
		else
			$user_name = $data['post_an_user'];
	
		if($data['avatar'] == '')
			$avatar = FWS_Path::client_app().'images/avatars/no_avatar.gif';
		else
			$avatar = FWS_Path::client_app().'images/avatars/'.$data['avatar'];
		
		return array(
			'date' => FWS_Date::get_date($data['post_time']),
			'username' => $user_name,
			'username_plain' => $data['post_an_user'],
			'lastpost_url' => $lastpost_url.'#b_'.$data['lastpost_id'],
			'topic_complete' => $tnamec,
			'topic' => $tnamed,
			'topic_url' => $topic_url,
			'avatar' => $avatar
		);
	}

	/**
	 * get the image and "read-status" of the forum with id = $id
	 *
	 * @param int $id the id of the forum
	 * @param boolean $is_unread will be set to the corresponding status
	 * @return string the link to with the read-status
	 */
	private static function _get_forum_image($id,&$is_unread)
	{
		$forums = FWS_Props::get()->forums();
		$unread = FWS_Props::get()->unread();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		
		static $readurl = null;
		if($readurl === null)
		{
			$readurl = BS_URL::get_mod_url('forums');
			$readurl->set(BS_URL_AT,BS_ACTION_CHANGE_READ_STATUS);
			$readurl->set(BS_URL_LOC,'read');
			$readurl->set(BS_URL_MODE,'forum');
			$readurl->set_sid_policy(BS_URL::SID_FORCE);
		}

		$data = $forums->get_node_data($id);
		$closed_data = $data->get_forum_is_closed();

		// unread forum?
		$is_unread = false;
		if($data->get_display_subforums())
			$is_unread = $unread->is_unread_forum($id);
		else
			$is_unread = $forums->is_unread_forum($id);

		$icon_set = 'fa';
		$icon_size = 'fa-2x';
		$icon_read = $icon_set.' fa-file-o '.$icon_size;
		$icon_unread = $icon_set.' fa-file-text-o '.$icon_size;
		$icon_closed_read = $icon_read.' fa-stack-1x';
		$icon_closed_unread = $icon_unread.' fa-stack-1x';
		$x_icon = $icon_set.' fa-times  fa-stack-1x bs_x_icon_correct';
		
		if($is_unread)
		{
			$readurl->set(BS_URL_FID,$id);
			
			if($closed_data)
				return '<span class="fa-stack"><i class="'.$icon_closed_unread.'" title="'.$locale->lang('forum_is_closed_msg').' '.$locale->lang('forum_is_unread').' '.$locale->lang('markrubrikasread').'"></i><i class="'.$x_icon.'" title="'.$locale->lang('forum_is_closed_msg').' '.$locale->lang('forum_is_unread').' '.$locale->lang('markrubrikasread').'"></i></span>';
			
			return '<a href="'.$readurl->to_url().'"><i class="'.$icon_unread.'" title="'.$locale->lang('forum_is_unread').' '.$locale->lang('markrubrikasread').'"></i></a>';
		}
		
		// denied forum?
		if($cfg['hide_denied_forums'] == 0 && !$auth->has_access_to_intern_forum($id))
			return '<span class="fa-stack"><i class="'.$icon_closed_read.'" title="'.$locale->lang('login_access_denied').'"></i><i class="'.$x_icon.'" title="'.$locale->lang('login_access_denied').'"></i></span>';

		// default
		if($closed_data)
			return '<span class="fa-stack"><i class="'.$icon_closed_read.'" title="'.$locale->lang('forum_is_closed_msg').' '.$locale->lang('forum_is_read').'"></i><i class="'.$x_icon.'" title="'.$locale->lang('forum_is_closed_msg').' '.$locale->lang('forum_is_read').'"></i></span>';

		return '<i class="'.$icon_read.'" title="'.$locale->lang('forum_is_read').'"></i>';
	}
}
?>