<?php
/**
 * Contains the general functions
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
 * Some general functions (which require the Boardsolution-objects)
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Functions extends FWS_Object
{
	/**
	 * checks wether the given user has access to the forum with given id
	 *
	 * @param int $uid the user-id (0 = guest)
	 * @param array $groups an array with all usergroups of the given user (empty = guest)
	 * @param int $fid the id of the forum
	 * @return boolean true if the user has access
	 */
	public function has_access_to_intern_forum($uid,$groups,$fid)
	{
		$forums = FWS_Props::get()->forums();
		$cache = FWS_Props::get()->cache();

		$forum_data = $forums->get_node_data($fid);
		if($forum_data === null)
			return false;
		
		if($forum_data->get_forum_is_intern())
		{
			// guests if never access to intern forums
			if($uid == 0)
				return false;
				
			// admins have always access
			if(in_array(BS_STATUS_ADMIN,$groups))
				return true;
			
			// check if the user-id or a group-id has access
			$rows = $cache->get_cache('intern')->get_elements_with(array('fid' => $fid));
			if(is_array($rows) && count($rows) > 0)
			{
				foreach($rows as $data)
				{
					if($data['access_type'] == 'user' && $data['access_value'] == $uid)
						return true;
					else if($data['access_type'] == 'group' && in_array($data['access_value'],$groups))
						return true;
				}
			}

			return false;
		}

		// it is no intern forum, so the user has access
		return true;
	}
	
	/**
	 * @return string the name of the folder of the default-language
	 */
	public function get_def_lang_folder()
	{
		$cache = FWS_Props::get()->cache();
		$cfg = FWS_Props::get()->cfg();

		static $folder = null;
		if($folder === null)
		{
			$data = $cache->get_cache('languages')->get_element($cfg['default_forum_lang']);
			$folder = $data['lang_folder'];
		}
		
		return $folder;
	}
	
	/**
	 * Checks wether <code>$_GET[BS_URL_SID] == $this->sess->session_id.</code>
	 *
	 * @return boolean true if the session-id is equal
	 */
	public function has_valid_get_sid()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();

		$get_sid = $input->get_var(BS_URL_SID,'get',FWS_Input::STRING);
		return $get_sid == $user->get_session_id();
	}
	
	/**
	 * checks wether the entered security code is equal to the code stored in the session
	 *
	 * the post-field has to have the name "security_code"
	 *
	 * @param boolean $require_enabled turn this on if you want to require that
	 * 								FWS_Props::get()->cfg()['enable_security_code'] is 1
	 * @return boolean true if the code is equal
	 */
	public function check_security_code($require_enabled = true)
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$input = FWS_Props::get()->input();

		// is the security-code disabled?
		if($require_enabled && $cfg['enable_security_code'] == 0)
			return true;

		// check the security-code
		$name = $user->get_session_data('sec_code_field');
		// the name has to be not empty
		if(!$name)
			return false;
		
		$security_code = $input->get_var($name,'post',FWS_Input::STRING);
		$session_code = $user->get_session_data('security_code');
		if($session_code != null)
		{
			if($session_code != FWS_String::strtoupper($security_code))
				return false;

			$user->delete_session_data('security_code');
		}
		else
			return false;

		return true;
	}
	
	/**
	 * Determines the statistics (grabs it from db, in most cases)
	 *
	 * @param boolean $complete wether you want to get the posts today, yesterday, logins etc., too
	 * @return array an array with the stats
	 */
	public function get_stats($complete = true)
	{
		$cache = FWS_Props::get()->cache();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();

		$stats_data = $cache->get_cache('stats')->current();
		
		// count user, forums, topics and posts
		$stats_data['total_users'] = BS_DAO::get_user()->get_user_count(1,0);
		$stats_data['total_topics'] = 0;
		$stats_data['posts_total'] = 0;
		$stats_data['total_forums'] = 0;
		foreach($forums->get_all_nodes() as $node)
		{
			$ndata = $node->get_data();
			$stats_data['total_topics'] += $ndata->get_threads();
			$stats_data['posts_total'] += $ndata->get_posts();
			$stats_data['total_forums']++;
		}
	
		if($complete)
		{
			$stats_data['posts_today'] = BS_DAO::get_posts()->get_count_today();
			$stats_data['posts_yesterday'] = BS_DAO::get_posts()->get_count_yesterday();
		
			if($stats_data['posts_last'] > 0)
				$stats_data['posts_last'] = FWS_Date::get_date($stats_data['posts_last']);
			else
				$stats_data['posts_last'] = $locale->lang('no_posts_found');
		
			if($stats_data['logins_last'] > 0)
				$stats_data['logins_last'] = FWS_Date::get_date($stats_data['logins_last']);
			else
				$stats_data['logins_last'] = $locale->lang('no_logins_found');
		}
		
		return $stats_data;
	}
	
	####################################################################
	# ------------------- GENERAL BOARD FUNCTIONS -------------------- #
	####################################################################
	
	/**
	 * determines the board-file
	 *
	 * @param boolean $append_sep if you enable this depending on the BS_FRONTEND_FILE-value
																it will be ? or &amp; appended
	 * @return string the board-file, for example 'index.php'
	 */
	public function get_board_file($append_sep = false)
	{
		$board_path = BS_FRONTEND_FILE;
		$board_path = str_replace('&','&amp;',$board_path);
		if($append_sep)
		{
			if(FWS_String::strpos(BS_FRONTEND_FILE,'?') !== false)
				$board_path .= '&amp;';
			else
				$board_path .= '?';
		}
	
		return $board_path;
	}
	
	/**
	 * claps a area with the given name
	 *
	 * @param string $name the name (without BS_COOKIE_PREFIX)
	 */
	public function clap_area($name)
	{
		$input = FWS_Props::get()->input();
		$cookies = FWS_Props::get()->cookies();

		$display = $input->get_var(BS_COOKIE_PREFIX.$name,'cookie',FWS_Input::INT_BOOL);
		if($display !== null && $display == 0)
			$display = 1;
		else
			$display = 0;
	
		$cookies->set_cookie($name,$display,86400 * 30);
	}
	
	/**
	 * builds the necessary data to clap an area
	 * 
	 * @param string $name the name of the area to clap
	 * @param string $url the URL for the clap-link
	 * @param string $display the display-value to show the block
	 * @return array an array of the form:
	 * 	<code>
	 * 		array(
	 * 			'divparams' => <additionalDivParameters>,
	 * 			'link' => <theLink>
	 * 		)
	 * 	</code>
	 */
	public function get_clap_data($name,$url,$display = 'table-row-group')
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();

		$clap_cookie = $input->get_var(BS_COOKIE_PREFIX.$name,'cookie',FWS_Input::INT_BOOL);
		$hide = ($clap_cookie === null || $clap_cookie == 1) ? '' : ' style="display: none;"';
	
		$clap_image = ($clap_cookie === null || $clap_cookie == 1) ? 'open' : 'closed';
		$image = $user->get_theme_item_path('images/cross'.$clap_image.'.gif');
		
		$link = '<a href="'.$url.'" onclick="clapArea(\''.$name.'\',\'clap_image_'.$name.'\'';
		$link .= ',\''.BS_COOKIE_PREFIX.$name.'\',\''.$display.'\'); return false;">';
		$link .= '<img id="clap_image_'.$name.'" src="'.$image.'" alt="Toggle" /></a>';
		
		return array(
			'divparams' => ' id="'.$name.'"'.$hide,
			'link' => $link
		);
	}
	
	/**
	 * claps the forum with given id
	 *
	 * @param int $id the id of the forum
	 */
	public function clap_forum($id)
	{
		$input = FWS_Props::get()->input();
		$cookies = FWS_Props::get()->cookies();

		$hidden_forums = $input->get_var(
			BS_COOKIE_PREFIX.'hidden_forums','cookie',FWS_Input::STRING
		);
		if(!$hidden_forums)
			$ids = array();
		else
			$ids = FWS_Array_Utils::advanced_explode(',',$hidden_forums);
	
		if(!FWS_Array_Utils::is_integer($ids))
			$ids = array();
	
		$index = array_search($id,$ids);
		if($index !== false)
			unset($ids[$index]);
		else
			$ids[] = $id;
	
		$hidden_forums = implode(',',$ids).',';
		$cookies->set_cookie('hidden_forums',$hidden_forums,86400 * 30);
	}
	
	/**
	 * checks wether the user is banned
	 *
	 * @param string $type the ban-type (mail,user,ip)
	 * @param string $value the value of the type of the current user
	 * @return boolean true if this user is banned
	 */
	public function is_banned($type,$value)
	{
		$cache = FWS_Props::get()->cache();

		foreach($cache->get_cache('banlist') as $data)
		{
			if($data['bann_type'] == $type)
			{
				if(FWS_String::strpos($data['bann_name'],'*') !== false)
				{
					$match = '';
					$parts = explode('*',$data['bann_name']);
					$len = count($parts);
					for($i = 0;$i < $len;$i++)
					{
						$match .= preg_quote($parts[$i],'/');
						if($i < $len - 1)
							$match .= '.*';
					}
	
					if($value && preg_match('/'.$match.'/',$value))
						return true;
				}
				else if($data['bann_name'] == $value)
					return true;
			}
		}
	
		return false;
	}
	
	/**
	 * displays a message to choose wether something should be deleted or not
	 *
	 * @param string $message the message to display
	 * @param string $yes_url the url for the yes-option
	 * @param string $no_url the url for the no-option
	 * @param string $delete_target the target-url for the form
	 */
	public function add_delete_message($message,$yes_url,$no_url,$delete_target = '')
	{
		$tpl = FWS_Props::get()->tpl();

		$tpl->set_template('inc_delete_message.htm');
		$tpl->add_variables(array(
			'delete_target' => $delete_target,
			'delete_message' => $message,
			'yes_url' => $yes_url,
			'no_url' => $no_url
		));
		$tpl->restore_template();
	}
	
	/**
	 * Builds the login-form (template inc_login.htm) for guests(!)
	 *
	 * @param boolean $display_denied_reasons do you want to display the denied-reasons? (default=true)
	 */
	public function build_login_form($display_denied_reasons = true)
	{
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$com = BS_Community_Manager::get_instance();

		$register_url = $com->get_register_url();
		$send_pw_url = $com->get_send_pw_url();
		$resend_act_link_url = $com->get_resend_act_url();

		$denied = '';
		if($display_denied_reasons)
		{
			$denied = '<ul>'."\n";
			foreach(array('intern','usergroup','deactivated') as $type)
				$denied .= '<li>'.$locale->lang('access_denied_reason_'.$type).'</li>'."\n";
			$denied .= '</ul>'."\n";
		}

		$login_msg = '';
		foreach(array('login','register','forgot_pw','activate') as $type)
		{
			switch($type)
			{
				case 'register':
					if($cfg['enable_registrations'] && $register_url)
					{
						$login_msg .= sprintf(
							' '.$locale->lang('login_message_'.$type),
							'<a href="'.$register_url.'">'.$locale->lang('here').'</a>'
						);
					}
					break;

				case 'forgot_pw':
					if($send_pw_url)
						$login_msg .= $locale->lang('login_message_'.$type);
					break;

				case 'activate':
					if($resend_act_link_url && $cfg['account_activation'] == 'email')
					{
						$login_msg .= sprintf(
							$locale->lang('login_message_'.$type),
							'<a href="'.$resend_act_link_url.'">'
								.$locale->lang('here').'</a>'
						);
					}
					break;

				default:
					$login_msg .= $locale->lang('login_message_'.$type);
					break;
			}
		}

		if($input->isset_var('login','post') || $display_denied_reasons)
			$title = $locale->lang('login_access_denied');
		else
			$title = $locale->lang('loginform');

		$tpl->set_template('inc_login.htm');
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_LOGIN,
			'target_url' => BS_URL::build_mod_url('login'),
			'show_register_link' => $cfg['enable_registrations'] && $register_url,
			'register_url' => $register_url,
			'send_pw_url' => $send_pw_url,
			'title' => $title,
			'login_msg' => $login_msg,
			'denied_msg' => $denied
		));
		$tpl->restore_template();
	}
	
	/**
	 * Builds the text for an "order-column"
	 *
	 * @param string $title the title of the column
	 * @param string $order_value the value of the order-parameter
	 * @param string $def_ascdesc the default value for BS_URL_AD (ASC or DESC)
	 * @param string $order the current value of BS_URL_ORDER
	 * @param BS_URL $url the current URL
	 * @return string the column-content
	 */
	public function get_order_column($title,$order_value,$def_ascdesc,$order,$url)
	{
		if(!($url instanceof BS_URL))
			FWS_Helper::def_error('instance','url','BS_URL',$url);
		
		$user = FWS_Props::get()->user();

		$url->set(BS_URL_ORDER,$order_value);
		
		if($order == $order_value)
		{
			$asc_img = $user->get_theme_item_path('images/asc.gif');
			$desc_img = $user->get_theme_item_path('images/desc.gif');
			$result = $title.' <a href="'.$url->set(BS_URL_AD,'ASC')->to_url().'">';
			$result .= '<img src="'.$asc_img.'" alt="ASC" />';
			$result .= '</a> ';
			$result .= '<a href="'.$url->set(BS_URL_AD,'DESC')->to_url().'">';
			$result .= '<img src="'.$desc_img.'" alt="DESC" />';
			$result .= '</a>';
		}
		else
			$result = '<a href="'.$url->set(BS_URL_AD,$def_ascdesc)->to_url().'">'.$title.'</a>';
	
		return $result;
	}
	
	####################################################################
	# ----------------------- OTHER FUNCTIONS ------------------------ #
	####################################################################
	
	/**
	 * returns the data of the rank with given points
	 *
	 * @param int $points the number of experience points
	 * @return array an associative array with the data of the rank
	 */
	public function get_rank_data($points)
	{
		$cache = FWS_Props::get()->cache();

		// points = 0 is a special case
		$ranks = $cache->get_cache('user_ranks');
		if($points == 0)
		{
			$ranks->rewind();
			$data = $ranks->current();
			$data['pos'] = 0;
			return $data;
		}
	
		for($i = 0;$data = $ranks->next();$i++)
		{
			if($data['post_from'] <= $points && $data['post_to'] >= $points)
			{
				$data['pos'] = $i;
				$ranks->rewind(); // IMPORTANT: rewind the position
				return $data;
			}
		}
	
		$ranks->rewind();
		$last = $ranks->get_element_count() - 1;
		$data = $ranks->get_element($last,false);
		$data['pos'] = $last;
		return $data;
	}
	
	/**
	 * generates the rank-images for the given user
	 *
	 * @param int $ranknum the total number of ranks
	 * @param int $rank_pos the position of the rank of the user
	 * @param int $user_id the id of the user
	 * @param string $group_ids the ids of the groups of the user
	 * @param boolean $is_mod force mod? (for the FAQ)
	 * @return string the images
	 */
	public function get_rank_images($ranknum,$rank_pos,$user_id,$group_ids,$is_mod = false)
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();

		$result = '';
		
		if($is_mod)
		{
			$images = array(
				'is_mod' => true,
				'filled' => $cfg['mod_rank_filled_image'],
				'empty' => $cfg['mod_rank_empty_image']
			);
		}
		else
			$images = $auth->get_user_images($user_id,$group_ids);
		
		if($images['is_mod'])
			$title = $locale->lang('moderators');
		else
			$title = $auth->get_groupname((int)$group_ids);
		
		for($i = 0;$i <= $rank_pos;$i++)
		{
			$result .= '<img alt="*" title="'.$title.'"';
			$result .= ' src="'.$user->get_theme_item_path($images['filled']).'" /> ';
		}
	
		for($i = 0;$i < ($ranknum - $rank_pos - 1);$i++)
		{
			$result .= '<img alt="O" title="'.$title.'"';
			$result .= ' src="'.$user->get_theme_item_path($images['empty']).'" /> ';
		}
	
		return $result;
	}
	
	/**
	 * determines the icon of the given file-extension
	 *
	 * @param string $extension the file-extension
	 * @return string the image-path to the icon of the given extension
	 */
	public function get_attachment_icon($extension)
	{
		switch($extension)
		{
			case 'gif':
			case 'jpeg':
			case 'jpg':
			case 'png':
				return FWS_Path::client_app().'images/filetypes/image.gif';
	
			case 'txt':
			case 'ini':
				return FWS_Path::client_app().'images/filetypes/text.gif';
	
			case 'pdf':
				return FWS_Path::client_app().'images/filetypes/pdf.gif';
	
			case 'htm':
			case 'html':
				return FWS_Path::client_app().'images/filetypes/html.gif';
	
			case 'zip':
			case 'rar':
			case 'tar':
			case 'gzip':
				return FWS_Path::client_app().'images/filetypes/archive.gif';
	
			case 'css':
				return FWS_Path::client_app().'images/filetypes/css.gif';
	
			case 'js':
				return FWS_Path::client_app().'images/filetypes/js.gif';
	
			default:
				return FWS_Path::client_app().'images/filetypes/unknown.gif';
		}
	}
	
	/**
	 * checks wether the given attachment has a valid extension
	 *
	 * @param string $attachment the filename of the attachment
	 * @return boolean true if the extension is valid
	 */
	public function check_attachment_extension($attachment)
	{
		$cfg = FWS_Props::get()->cfg();

		if($cfg['attachments_filetypes'] == '')
			return false;
	
		$extension = FWS_FileUtils::get_extension($attachment);
		$types = explode('|',FWS_String::strtolower($cfg['attachments_filetypes']));
		return in_array($extension,$types);
	}
	
	/**
	 * builds the prefix for the attachment-image at the pm-overview
	 * 
	 * @param int $attachments the number of attachments
	 * @return string the prefix
	 */
	public function get_pm_attachment_prefix($attachments)
	{
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		static $img_attachment = '';
		if(!$img_attachment)
			$img_attachment = $user->get_theme_item_path('images/attachment.gif');
		
		if($attachments > 0)
		{
			if($attachments == 1)
				$a_title = $attachments.' '.$locale->lang('attachment');
			else
				$a_title = $attachments.' '.$locale->lang('attachments');
			
			$prefix = '<img src="'.$img_attachment.'" alt="'.$a_title.'" title="'.$a_title.'" /> ';
		}
		else
			$prefix = '';
		
		return $prefix;
	}
	
	/**
	 * deletes the attachment with given path including the thumbnail
	 *
	 * @param string $path the path of the attachment (without FWS_Path::server_app())
	 */
	public function delete_attachment($path)
	{
		// delete the thumbnail, if it exists
		$ext = FWS_FileUtils::get_extension($path,false);
		$start = FWS_String::substr($path,0,FWS_String::strlen($path) - FWS_String::strlen($ext) - 1);
		if(is_file(FWS_Path::server_app().$start.'_thumb.'.$ext))
			@unlink(FWS_Path::server_app().$start.'_thumb.'.$ext);
	
		@unlink(FWS_Path::server_app().$path);
	}
	
	/**
	 * generates the rating-diagram for a link in the linklist
	 *
	 * @param int $total the total number of points
	 * @param int $votes the total number of votes
	 * @param boolean $text add text?
	 * @param int $multi the multiplicator for the image-width
	 * @return string the result
	 */
	public function get_link_rating($total,$votes,$text = 1,$multi = 2)
	{
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		static $images = null;
		if($images === null)
		{
			$images = array(
				'rate_back' => $user->get_theme_item_path('images/diagrams/rate_back.gif'),
				'rate_grey' => $user->get_theme_item_path('images/diagrams/rate_grey.gif'),
				'rate_red' => $user->get_theme_item_path('images/diagrams/rate_red.gif'),
				'rate_blue' => $user->get_theme_item_path('images/diagrams/rate_blue.gif')
			);
		}
		
		if($votes == 0)
			$result = 6;
		else
			$result = 5 - (@round($total / $votes,2) - 1);
		
		if($result == 0)
			$pro = 0;
		else
			$pro = @round(100 / (5 / $result),0) * $multi;
		$text_ins = '('.$locale->lang('school_grade').': '.abs($result - 6).' | ';
		$text_ins .= $votes.' '.$locale->lang('votes').')';
		
		$img = ($votes == 0) ? 'grey' : 'red';
		if($votes == 0)
			$pro = 0;
	
		$res = '<img src="'.$images['rate_back'].'" alt="" />';
		$res .= '<img src="'.$images['rate_blue'].'" width="'.$pro.'" height="10"';
		$res .= ($text == 0) ? ' title="'.$text_ins.'" alt="'.$text_ins.'"' : '';
		$res .= ' /><img src="'.$images['rate_'.$img].'"';
		$res .= ' height="10" width="'.(($multi * 100) - $pro).'"';
		$res .= ($text == 0) ? ' title="'.$text_ins.'" alt="'.$text_ins.'"' : '';
		$res .= ' /><img src="'.$images['rate_back'].'" alt="" /> (';
		$res .= ($text == 1) ? $locale->lang('school_grade').': ' : '';
		$res .= abs($result - 6).' | '.$votes;
		$res .= ($text == 1) ? ' '.$locale->lang('votes') : '';
		$res .= ')';
		
		return $res;
	}
	
	/**
	 * returns the number of votes and the type of the poll
	 *
	 * @param int $id the id of the poll
	 * @return array an array of the form:
	 * 	<code>
	 * 		array(
	 * 			'total_votes' => <total_votes>,
	 * 			'poll_type' => <type>
	 * 		)
	 * 	</code>
	 */
	public function get_poll_info($id)
	{
		$total = 0;
		$multichoice = 0;
		foreach(BS_DAO::get_polls()->get_options_by_id($id) as $data)
		{
			$multichoice = $data['multichoice'];
			$total += $data['option_value'];
		}
	
		return array('total_votes' => $total,'multichoice' => $multichoice);
	}
	
	/**
	 * Returns an instance of the appropriate mail-class with the given parameters
	 * 
	 * @param string $receiver the receiver
	 * @param string $subject the subject of the mail
	 * @param string $message the message
	 * @return FWS_Email_Base the email-class
	 */
	public function get_mailer($receiver = '',$subject = '',$message = '')
	{
		$cfg = FWS_Props::get()->cfg();

		// use php-mail()?
		if($cfg['mail_method'] == 'mail')
			$c = new FWS_Email_PHP($receiver,$subject,$message);
		else
		{
			$c = new FWS_Email_SMTP($receiver,$subject,$message);
			// set SMTP-properties
			$c->set_smtp_host($cfg['smtp_host']);
			$c->set_smtp_port($cfg['smtp_port']);
			$c->set_smtp_login($cfg['smtp_login']);
			$c->set_smtp_password($cfg['smtp_password']);
		}

		// set basic properties for BS
		$c->set_xmailer(BS_VERSION);
		$c->set_from($cfg['board_email']);
		$c->set_charset(BS_HTML_CHARSET);
		
		return $c;
	}

	/**
	 * Connects to the MySQL-DB with some basic commands
	 *
	 * @param string $host the hostname. May contain the port
	 * @param string $login the loginname
	 * @param string $password the password
	 * @param string $database the databasename
	 * @return the db connection
	 */
	public function connect_to_db($host, $login, $password, $database)
	{
		$c = new FWS_DB_MySQLi_Connection();

		if($c->is_connected())
			return $c;
		
		$c->connect($host,$login,stripslashes(html_entity_decode($password, ENT_QUOTES, BS_HTML_CHARSET)));
		$c->select_database($database);
		$c->set_use_transactions(BS_USE_TRANSACTIONS);
		$c->set_save_queries(BS_DEBUG > 1);
		// we don't want to escape them because we use the input-class to do so.
		// before query-execution would be better but it is too dangerous to change that now :/
		$c->set_escape_values(false);
		
		$version = $c->get_server_version();
		if($version >= '4.1')
		{
			$c->execute('SET CHARACTER SET '.BS_DB_CHARSET.';');
			// we don't want to have any sql-modes
			$c->execute('SET SESSION sql_mode="";');
		}

		return $c;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
