<?php
/**
 * Contains the functions for the forums
 * 
 * @version			$Id: forumutils.php 705 2008-05-15 10:14:58Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Some functions for the forums
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ForumUtils extends PLIB_Singleton
{
	/**
	 * @return BS_ForumUtils the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Builds all childforums of the given parent-id (0 = all forums)
	 *
	 * @param int $parent_id the parent-id
	 * @return string the html-code
	 */
	public function get_forum_list($parent_id)
	{
		if($this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING) == 'clapforum' &&
			 ($id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID)) != null)
			$this->functions->clap_forum($id);
	
		$start_layer = $parent_id == 0 ? 1 : $this->forums->get_node($parent_id)->get_layer() + 2;
		$num = 0;
		$cookie = $this->input->get_var(BS_COOKIE_PREFIX.'hidden_forums','cookie',PLIB_Input::STRING);
		if($cookie == null)
			$cookie = $this->input->set_var(BS_COOKIE_PREFIX.'hidden_forums','cookie','');
	
		$hidden_forums = ($cookie != '') ? explode(',',$cookie) : array();
		$sub_nodes = $this->forums->get_sub_nodes($parent_id);
	
		// determine the number of visible forums
		$forum_num = count($sub_nodes);
		$real_num = $forum_num;
		for($i = 0;$i < $forum_num;$i++)
		{
			if($this->cfg['hide_denied_forums'] == 1 &&
				!$this->auth->has_access_to_intern_forum($sub_nodes[$i]->get_id()))
				$real_num--;
		}
	
		$open_div = false;
		$clap_forum = false;
	
		// are forums available?
		if($real_num == 0)
		{
			// just show the message if we are displaying all forums (not just sub-forums)
			if($parent_id == 0)
				$this->msgs->add_notice($this->locale->lang('no_forums_available'));
		}
		else
		{
			$this->tpl->set_template('inc_forums.htm',0);
			
			$this->tpl->add_variables(array(
				'title' => ($parent_id == 0) ? $this->locale->lang('forums') : $this->locale->lang('subdirs'),
				'enable_moderators' => $this->cfg['enable_moderators'],
			));
	
			$images = array(
				'dot' => $this->user->get_theme_item_path('images/forums/path_dot.gif'),
				'middle' => $this->user->get_theme_item_path('images/forums/path_middle.gif')
			);
	
			$forums = array();
			$fn = 0;
	
			$post_order = BS_PostingUtils::get_instance()->get_posts_order();
			$next_display_layer = -1;
			$sub_cats = array();
			for($i = 0;$i < $forum_num;$i++)
			{
				$node = $sub_nodes[$i];
				$daten = $node->get_data();
				/* @var $node PLIB_Tree_Node */
				/* @var $daten BS_Forums_NodeData */
				$forum_id = $daten->get_id();
				$forum_type_cats = $daten->get_forum_type() == 'contains_cats';
				$clap_forum = $forum_type_cats && $node->get_layer() - $start_layer == -1;
	
				if($node->get_layer() <= $next_display_layer)
					$next_display_layer = -1;
	
				// do we want to display this forum?
				if($next_display_layer == -1)
				{
					if(!$daten->get_display_subforums())
						$next_display_layer = $node->get_layer();
	
					// skip this forum if the user is not allowed to view it
					if($this->cfg['hide_denied_forums'] == 1 &&
						 !$this->auth->has_access_to_intern_forum($forum_id))
						continue;
	
					if(!isset($sub_cats[$daten->get_parent_id()]))
						$sub_cats[$daten->get_parent_id()] = 1;
					else
						$sub_cats[$daten->get_parent_id()]++;
	
					// hide this category?
					if(in_array($forum_id,$hidden_forums))
					{
						$display_rubrik = 'none';
						$img_ins = $this->user->get_theme_item_path('images/crossclosed.gif');
					}
					else
					{
						$display_rubrik = 'block';
						$img_ins = $this->user->get_theme_item_path('images/crossopen.gif');
					}
	
					$close_clap_forum = false;
					if($clap_forum && $num > 0)
					{
						$close_clap_forum = true;
						$open_div = false;
					}
	
					// build the "forum-path"
					$pimages = array();
					if($node->get_layer() > 0)
					{
						$path_images = $this->get_path_images($node,$sub_cats,$images,$start_layer);
						for($b = 0;$b < count($path_images);$b++)
						{
							$pimages[] = array(
								'image' => $path_images[$b]
							);
						}
					}
	
					$fid_add = $parent_id != 0 ? '&amp;'.BS_URL_FID.'='.$parent_id : '';
					$forum_url = $this->url->get_topics_url($forum_id,'&amp;',1);
					
					$is_unread = false;
					if($forum_type_cats)
					{
						if(!$daten->get_display_subforums())
							$clap_forum = false;
						
						$forums[$fn] = array(
							'contains_forums' => true,
							'forum_id' => $forum_id,
							'img_ins' => $img_ins,
							'display_rubrik' => $display_rubrik,
							'clap_forum' => $clap_forum,
							'clap_forum_url' => $this->url->get_url(
								0,$fid_add.'&amp;'.BS_URL_LOC.'=clapforum&amp;'.BS_URL_ID.'='.$forum_id
							),
							'cookie_prefix' => BS_COOKIE_PREFIX,
							'root_path' => PLIB_Path::inner()
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
	
						if(!$daten->get_display_subforums())
						{
							$info = $this->_get_subforum_info($daten->get_id());
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
	
						$forums[$fn] = array(
							'contains_forums' => false,
							'clap_forum' => $clap_forum,
							'mods_ins' => $this->auth->get_forum_mods($forum_id),
							'beschr_ins' => $beschr_ins,
							'lastpost' => $this->_get_forum_lastpost($lp_data,$post_order),
							'alreadyread' => $this->_get_forum_image($forum_id,$is_unread),
							'thread_count' => $thread_count,
							'post_count' => $post_count,
							'sub_forums' => $sub_forums
						);
					}
					
					$forums[$fn]['is_unread'] = $is_unread;
					$forums[$fn]['close_clap_forum'] = $close_clap_forum;
					$forums[$fn]['forum_name_ins'] = $daten->get_name();
					$forums[$fn]['forum_url'] = $forum_url;
					$forums[$fn]['path_images'] = $pimages;
					
					$fn++;
				}
	
				if($clap_forum)
					$open_div = true;
			}
			
			$this->tpl->add_array('forums',$forums);
			$this->tpl->add_variables(array(
				'clap_forum_bottom' => $open_div,
				'forum_cookie' => $this->input->get_var(
					BS_COOKIE_PREFIX.'hidden_forums','cookie',PLIB_Input::STRING
				)
			));
			
			$this->tpl->restore_template();
		}
	}
	
	/**
	 * collects all denied forums
	 *
	 * @param boolean $include_categories do you want to include categories?
	 * @return array an numeric array with the ids of the denied forums
	 */
	public function get_denied_forums($include_categories = true)
	{
		$ugroup = $this->user->get_user_group();
		if($ugroup == BS_STATUS_ADMIN && !$include_categories)
			return array();
		
		$denied = array();
		$intern_access = $this->get_intern_forum_permissions();
		foreach($this->forums->get_all_nodes() as $forum)
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
			if($ugroup == BS_STATUS_ADMIN)
				continue;
	
			// is it an intern forum?
			if(!$this->forums->is_intern_forum($fid))
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
	public function get_intern_forum_permissions()
	{
		$result = array();
		$all_groups = $this->user->get_all_user_groups();
		foreach($this->cache->get_cache('intern') as $data)
		{
			if(!isset($result[$data['fid']]) || !$result[$data['fid']])
			{
				if($data['access_type'] == 'group')
					$result[$data['fid']] = in_array($data['access_value'],$all_groups);
				else
					$result[$data['fid']] = $this->user->get_user_id() == $data['access_value'];
			}
		}
	
		return $result;
	}
	
	/**
	 * generates the path to a forum and returns it
	 *
	 * @param int $fid the id of the forum
	 * @param boolean $start_with_raquo if you enable this the path will start with &amp;raquo;
	 * @return string the result
	 */
	public function get_forum_path($rid = 0,$start_with_raquo = true)
	{
		$id = ($rid != 0) ? $rid : $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$res = '';
		if($id != null)
		{
			$path = $this->forums->get_path($id);
			$len = count($path);
			for($i = $len - 1;$i >= 0;$i--)
			{
				if($i < $len - 1 || $start_with_raquo)
					$res .= ' &raquo; ';
				$res .= '<a href="'.$this->url->get_topics_url($path[$i][1]).'"';
	
				if(PLIB_String::strlen($path[$i][0]) > BS_MAX_FORUM_TITLE_LENGTH)
				{
					$res .= ' title="'.$path[$i][0].'">';
					$res .= PLIB_String::substr($path[$i][0],0,BS_MAX_FORUM_TITLE_LENGTH - 3) . ' ...';
				}
				else
					$res .= '>'.$path[$i][0];
	
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
	 * @param int $select the id of the selected forum
	 * @param int $disabled_forum the id of the forum to disable
	 * @param boolean $disable_categories do you want to disable categories?
	 * @param boolean $add_all_forums_option do you want to add an "all"-option?
	 * @return string the options of the combobox
	 */
	public function get_recursive_forum_combo($name,$select,$disabled_forum,
		$disable_categories = true,$add_all_forums_option = false)
	{
		$denied = $this->get_denied_forums(false);
		$multiple = PLIB_String::substr($name,-2,2) == '[]';
		
		if($multiple)
			$result = '<select name="'.$name.'" multiple="multiple" size="10">'."\n";
		else
			$result = '<select name="'.$name.'">'."\n";
		
		if($add_all_forums_option)
		{
			$selected = ($multiple && $select != null && in_array(0,$select)) || $select === 0;
			$result .= '	<option value="0"'.($selected ? ' selected="selected"' : '').'>- ';
			$result .= $this->locale->lang('all').' '.$this->locale->lang('forums').' -</option>'."\n";
		}
		
		$forums = $this->forums->get_all_nodes();
		$len = count($forums);
		for($i = 0;$i < $len;$i++)
		{
			$node = $forums[$i];
			$fdata = $node->get_data();
			$fid = $fdata->get_id();
			
			// is the forum denied?
			if(in_array($fid,$denied))
			{
				if($this->cfg['hide_denied_forums'] == 1)
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
	 * @param PLIB_Tree_Node $node the node of the forum
	 * @param array $sub_cats an associative array of the form:
	 * 	<code>array(<parent_id> => <sub_id>)</code>
	 * @param array $images an array with the images: <code>array('dot' => ...,'middle' => ...)</code>
	 * @param int $start_layer the start-layer
	 * @return array an numeric array with the path-images.
	 * 	each array-entry represents one "path-layer" beginning at the front
	 */
	public function get_path_images($node,$sub_cats,$images,$start_layer = 0)
	{
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
				$bigger = !isset($sub_cats[$p_id]) || $this->forums->get_child_count($p_id) > $sub_cats[$p_id];
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
					$p_id = $this->forums->get_parent_id($p_id);
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
	private function _get_subforum_info($parent_id)
	{
		$thread_num = 0;
		$post_num = 0;
		$sub_forums = "";
		$sub_forums_count = 1;
		$lastpost = array(0,0,"","",0,0,0,0,0);
		if($this->forums->has_childs($parent_id))
		{
			$forums = $this->forums->get_sub_nodes($parent_id);
			for($i = 0;$i < count($forums);$i++)
			{
				$node = $forums[$i];
				$daten = $node->get_data();
				$fid = $daten->get_id();
				
				if($this->auth->has_access_to_intern_forum($fid))
				{
					if($node->get_layer() == 2 && $sub_forums_count <= BS_FORUM_SMALL_SUBDIR_DISPLAY)
					{
						$url = $this->url->get_topics_url($fid);
						$sub_forums .= "<a href=\"".$url."\">".$daten->get_name()."</a>, ";
						$sub_forums_count++;
					}
					else if($sub_forums_count == BS_FORUM_SMALL_SUBDIR_DISPLAY + 1)
					{
						$sub_forums .= " ..., ";
						$sub_forums_count++;
					}
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
						$daten->get_lastpost_usergroups()
					);
				}
				
				$thread_num += $daten->get_threads();
				$post_num += $daten->get_posts();
			}
		}
	
		if($sub_forums != '')
			$sub_forums = PLIB_String::substr($sub_forums,0,PLIB_String::strlen($sub_forums) - 2);
			
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
	 * @return array the lastpost-informations
	 */
	private function _get_forum_lastpost($data,$post_order)
	{
		if(!isset($data['tposts']))
			$data['tposts'] = 0;
	
		if($data['lastpost_id'] == 0)
			return false;
	
		$pages = BS_PostingUtils::get_instance()->get_post_pages($data['tposts'] + 1);
		$topic_name = BS_TopicUtils::get_instance()->get_displayed_name($data['threadname'],
			BS_MAX_TOPIC_LENGTH_LAST_POST);
	
		// generate url
		$site = 1;
		if($post_order == 'ASC' && $pages > 1)
			$site = $pages;
		$topic_url = $this->url->get_posts_url($data['id'],$data['threadid'],'&amp;',1);
		if($site > 1)
			$lastpost_url = $this->url->get_posts_url($data['id'],$data['threadid'],'&amp;',$site);
		else
			$lastpost_url = $topic_url;
	
		// determine username
		if($data['post_user'] != 0)
		{
			$user_name = BS_UserUtils::get_instance()->get_link(
				$data['post_user'],$data['username'],$data['user_group']
			);
		}
		else
			$user_name = $data['post_an_user'];
	
		return array(
			'date' => PLIB_Date::get_date($data['post_time']),
			'username' => $user_name,
			'lastpost_url' => $lastpost_url.'#b_'.$data['lastpost_id'],
			'topic_complete' => $topic_name['complete'],
			'topic' => $topic_name['displayed'],
			'topic_url' => $topic_url
		);
	
		return $res;
	}

	/**
	 * get the image and "read-status" of the forum with id = $id
	 *
	 * @param int $id the id of the forum
	 * @param boolean $is_unread will be set to the corresponding status
	 * @return string the link to with the read-status
	 */
	private function _get_forum_image($id,&$is_unread)
	{
		$data = $this->forums->get_node_data($id);

		// unread forum?
		$is_unread = false;
		if($data->get_display_subforums())
			$is_unread = $this->unread->is_unread_forum($id);
		else
			$is_unread = $this->forums->is_unread_forum($id);

		if($is_unread)
		{
			$image = $data->get_forum_is_closed() ? 'forum_unread_closed' : 'forum_unread';
			$action_type = '&amp;'.BS_URL_AT.'='.BS_ACTION_CHANGE_READ_STATUS;
			$read_url = $this->url->get_url(
				'forums',$action_type.'&amp;'.BS_URL_LOC.'=read&amp;'.BS_URL_MODE.'=forum'
					.'&amp;'.BS_URL_FID.'='.$id,'&amp;',true
			);
			$img = $this->user->get_theme_item_path('images/unread/'.$image.'.gif');
			return '<a href="'.$read_url.'"><img src="'.$img.'"'
				.' alt="'.$this->locale->lang('markrubrikasread').'"'
				.' title="'.$this->locale->lang('markrubrikasread').'" border="0" /></a>';
		}

		// denied forum?
		if($this->cfg['hide_denied_forums'] == 0 && !$this->auth->has_access_to_intern_forum($id))
		{
			$img = $this->user->get_theme_item_path('images/unread/forum_denied.gif');
			return '<img src="'.$img.'" alt="'.$this->locale->lang('login_access_denied').'"'
				.' title="'.$this->locale->lang('login_access_denied').'" />';
		}

		// default
		$image = $data->get_forum_is_closed() ? 'forum_read_closed' : 'forum_read';
		$message = $data->get_forum_is_closed() ? 'forum_is_closed_msg' : 'forum_is_read';
		$img = $this->user->get_theme_item_path('images/unread/'.$image.'.gif');
		return '<img src="'.$img.'" alt="'.$this->locale->lang($message).'"'
		 .' title="'.$this->locale->lang($message).'" />';
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>