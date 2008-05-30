<?php
/**
 * Contains the general functions
 * 
 * @version			$Id: functions.php 787 2008-05-28 14:58:33Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Some general functions (which require the Boardsolution-objects)
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Functions extends PLIB_FullObject
{
	/**
	 * Checks wether <code>$_GET[BS_URL_SID] == $this->sess->session_id.</code>
	 *
	 * @return boolean true if the session-id is equal
	 */
	public function has_valid_get_sid()
	{
		$get_sid = $this->input->get_var(BS_URL_SID,'get',PLIB_Input::STRING);
		return $get_sid == $this->user->get_session_id();
	}
	
	/**
	 * Builds the start-url for the current user
	 * 
	 * @return string the start-url
	 */
	public function get_start_url()
	{
		if($this->cfg['enable_portal'] == 1 &&
			($this->user->is_loggedin() || $this->user->get_profile_val('startmodule') == 'portal'))
			return $this->url->get_portal_url();
		
		return $this->url->get_forums_url();
	}
	
	/**
	 * checks wether the entered security code is equal to the code stored in the session
	 *
	 * the post-field has to have the name "security_code"
	 *
	 * @param boolean $require_enabled turn this on if you want to require that
	 * 								$this->cfg['enable_security_code'] is 1
	 * @return boolean true if the code is equal
	 */
	public function check_security_code($require_enabled = true)
	{
		// is the security-code disabled?
		if($require_enabled && $this->cfg['enable_security_code'] == 0)
			return true;

		// check the security-code
		$name = $this->user->get_session_data('sec_code_field');
		$security_code = $this->input->get_var($name,'post',PLIB_Input::STRING);
		$session_code = $this->user->get_session_data('security_code');
		if($session_code != null)
		{
			if($session_code != PLIB_String::strtoupper($security_code))
				return false;

			$this->user->delete_session_data('security_code');
		}
		else
			return false;

		return true;
	}

	/**
	 * Generates the pagination from the given object
	 *
	 * @param PLIB_Pagination $pagination the PLIB_Pagination-object
	 * @param string $url the URL containing {d} at the position where to put the page-number
	 * @return string the result
	 */
	public function add_pagination($pagination,$url)
	{
		if(!($pagination instanceof PLIB_Pagination))
			PLIB_Helper::def_error('instance','pagination','PLIB_Pagination',$pagination);
		
		if(empty($url))
			PLIB_Helper::def_error('empty','url',$url);
		
		if($this->cfg['show_always_page_split'] == 1 || $pagination->get_page_count() > 1)
		{
			$page = $pagination->get_page();
			$numbers = $pagination->get_page_numbers();
			$tnumbers = array();
			foreach($numbers as $n)
			{
				$number = $n;
				$link = '';
				if(PLIB_Helper::is_integer($n))
					$link = str_replace('{d}',$n,$url);
				else
					$link = '';
				$tnumbers[] = array(
					'number' => $number,
					'link' => $link
				);
			}
			
			$start_item = $pagination->get_start() + 1;
			$end_item = $start_item + $pagination->get_per_page() - 1;
			$end_item = ($end_item > $pagination->get_num()) ? $pagination->get_num() : $end_item;
			
			$this->tpl->set_template('inc_page_split.htm');
			$this->tpl->add_array('numbers',$tnumbers);
			$this->tpl->add_variables(array(
				'page' => $page,
				'total_pages' => $pagination->get_page_count(),
				'start_item' => $start_item,
				'end_item' => $end_item,
				'total_items' => $pagination->get_num(),
				'prev_url' => str_replace('{d}',$page - 1,$url),
				'next_url' => str_replace('{d}',$page + 1,$url),
				'first_url' => str_replace('{d}',1,$url),
				'last_url' => str_replace('{d}',$pagination->get_page_count(),$url)
			));
			$this->tpl->restore_template();
		}
	}
	
	/**
	 * A small version of the pagination
	 *
	 * @param PLIB_Pagination $pagination the PLIB_Pagination-object
	 * @param string $link the URL containing {d} at the position where to put the page-number
	 * @return string the pagination
	 */
	public function get_pagination_small($pagination,$link)
	{
		$res = '';
		$page = $pagination->get_page();
		$numbers = $pagination->get_page_numbers();
		foreach($numbers as $n)
		{
			if(PLIB_Helper::is_integer($n))
			{
				if($n == $page)
					$res .= $n.' ';
				else
					$res .= '<a href="'.str_replace('{d}',$n,$link).'">'.$n.'</a> ';
			}
			else
				$res .= ' '.$n.' ';
		}
	
		return $res;
	}
	
	/**
	 * a very small version of BS_get_page_split :)
	 *
	 * @param int $total_pages the total number of pages
	 * @param string $link the URL containing {d} at the position where to put the page-number
	 * @return string the page-split
	 */
	public function get_page_split_tiny($total_pages,$link)
	{
		$result = '';
		if($total_pages > 1)
		{
			$result = '[ '.$this->locale->lang('pages').': ';
			for($i = 1;$i <= $total_pages;$i++)
			{
				if($i < 5)
					$result .= '<a href="'.str_replace('{d}',$i,$link).'">'.$i.'</a> ';
			}
	
			if($total_pages > 5)
				$result .= ' ... ';
	
			if($total_pages > 4)
			{
				$result .= '<a href="'.str_replace('{d}',$total_pages,$link).'">';
				$result .= $total_pages.'</a>';
			}
	
			$result .= ' ]';
		}
		return $result;
	}
	
	/**
	 * Determines the statistics (grabs it from db, in most cases)
	 *
	 * @param boolean $complete wether you want to get the posts today, yesterday, logins etc., too
	 * @return array an array with the stats
	 */
	public function get_stats($complete = true)
	{
		$stats_data = $this->cache->get_cache('stats')->current();
		
		// count user, forums, topics and posts
		$stats_data['total_users'] = BS_DAO::get_user()->get_user_count(1,0);
		$stats_data['total_topics'] = 0;
		$stats_data['posts_total'] = 0;
		$stats_data['total_forums'] = 0;
		foreach($this->forums->get_all_nodes() as $node)
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
				$stats_data['posts_last'] = PLIB_Date::get_date($stats_data['posts_last']);
			else
				$stats_data['posts_last'] = $this->locale->lang('no_posts_found');
		
			if($stats_data['logins_last'] > 0)
				$stats_data['logins_last'] = PLIB_Date::get_date($stats_data['logins_last']);
			else
				$stats_data['logins_last'] = $this->locale->lang('no_logins_found');
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
		if($append_sep)
		{
			if(PLIB_String::strpos(BS_FRONTEND_FILE,'?') !== false)
				$board_path .= '&amp;';
			else
				$board_path .= '?';
		}
	
		return $board_path;
	}
	
	/**
	 * claps a area with the given name
	 *
	 * @param string name the name (without BS_COOKIE_PREFIX)
	 */
	public function clap_area($name)
	{
		$display = $this->input->get_var(BS_COOKIE_PREFIX.$name,'cookie',PLIB_Input::INT_BOOL);
		if($display !== null && $display == 0)
			$display = 1;
		else
			$display = 0;
	
		$this->cookies->set_cookie($name,$display,86400 * 30);
	}
	
	/**
	 * builds the necessary data to clap an area
	 * 
	 * @param string $name the name of the area to clap
	 * @param string $url the URL for the clap-link
	 * @param string $display the display-value to show the block
	 * @return an array of the form:
	 * 	<code>
	 * 		array(
	 * 			'divparams' => <additionalDivParameters>,
	 * 			'link' => <theLink>
	 * 		)
	 * 	</code>
	 */
	public function get_clap_data($name,$url,$display = 'table-row-group')
	{
		$clap_cookie = $this->input->get_var(BS_COOKIE_PREFIX.$name,'cookie',PLIB_Input::INT_BOOL);
		$hide = ($clap_cookie === null || $clap_cookie == 1) ? '' : ' style="display: none;"';
	
		$clap_image = ($clap_cookie === null || $clap_cookie == 1) ? 'open' : 'closed';
		$image = $this->user->get_theme_item_path('images/cross'.$clap_image.'.gif');
		
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
		$hidden_forums = $this->input->get_var(
			BS_COOKIE_PREFIX.'hidden_forums','cookie',PLIB_Input::STRING
		);
		if(!$hidden_forums)
			$ids = array();
		else
			$ids = PLIB_Array_Utils::advanced_explode(',',$hidden_forums);
	
		if(!PLIB_Array_Utils::is_integer($ids))
			$ids = array();
	
		$index = array_search($id,$ids);
		if($index !== false)
			unset($ids[$index]);
		else
			$ids[] = $id;
	
		$hidden_forums = implode(',',$ids).',';
		$this->set_cookie('hidden_forums',$hidden_forums,86400 * 30);
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
		foreach($this->cache->get_cache('banlist') as $data)
		{
			if($data['bann_type'] == $type)
			{
				if(PLIB_String::strpos($data['bann_name'],'*') !== false)
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
	
					if(preg_match('/'.$match.'/',$value))
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
		$this->tpl->set_template('inc_delete_message.htm',0);
		$this->tpl->add_variables(array(
			'delete_target' => $delete_target,
			'delete_message' => $message,
			'yes_url' => $yes_url,
			'no_url' => $no_url
		));
		$this->tpl->restore_template();
	}
	
	/**
	 * Signalizes the document that the login form should be displayed (instead of the module)
	 * The denied-message will be displayed if the user is loggedin or a login-form
	 * if the user is loggedout
	 *
	 * @param boolean $display_denied_reasons do you want to display the denied-reasons? (default=true)
	 * @return string the login-form
	 */
	public function show_login_form($display_denied_reasons = true)
	{
		if(!$this->user->is_loggedin())
		{
			if($this->cfg['enable_registrations'] && !BS_ENABLE_EXPORT)
				$register_url = $this->url->get_url('register');
			else if($this->cfg['enable_registrations'] && BS_EXPORT_REGISTER_TYPE == 'link')
				$register_url = BS_EXPORT_REGISTER_LINK;
			else
				$register_url = '';
	
			if(!BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE == 'enabled')
				$send_pw_url = $this->url->get_url('sendpw');
			else if(BS_EXPORT_SEND_PW_TYPE == 'link')
				$send_pw_url = BS_EXPORT_SEND_PW_LINK;
			else
				$send_pw_url = '';
			
			if(!BS_ENABLE_EXPORT)
				$resend_act_link_url = $this->url->get_url('resend_activation');
			else if(BS_EXPORT_RESEND_ACT_TYPE == 'link')
				$resend_act_link_url = BS_EXPORT_RESEND_ACT_LINK;
			else
				$resend_act_link_url = '';
	
			$denied = '';
			if($display_denied_reasons)
			{
				$denied = '<ul>'."\n";
				foreach(array('intern','usergroup','deactivated') as $type)
					$denied .= '<li>'.$this->locale->lang('access_denied_reason_'.$type).'</li>'."\n";
				$denied .= '</ul>'."\n";
			}
	
			$login_msg = '';
			foreach(array('login','register','forgot_pw','activate') as $type)
			{
				switch($type)
				{
					case 'register':
						if($this->cfg['enable_registrations'] &&
							(!BS_ENABLE_EXPORT || BS_EXPORT_REGISTER_TYPE == 'link'))
						{
							$login_msg .= sprintf(
								' '.$this->locale->lang('login_message_'.$type),
								'<a href="'.$register_url.'">'.$this->locale->lang('here').'</a>'
							);
						}
						break;
	
					case 'forgot_pw':
						if(!BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE != 'disabled')
							$login_msg .= $this->locale->lang('login_message_'.$type);
						break;
	
					case 'activate':
						if(!BS_ENABLE_EXPORT || BS_EXPORT_RESEND_ACT_TYPE == 'link')
						{
							if($this->cfg['account_activation'] == 'email')
							{
								$login_msg .= sprintf(
									$this->locale->lang('login_message_'.$type),
									'<a href="'.$resend_act_link_url.'">'
										.$this->locale->lang('here').'</a>'
								);
							}
						}
						break;
	
					default:
						$login_msg .= $this->locale->lang('login_message_'.$type);
						break;
				}
			}
	
			if($this->input->isset_var('login','post') || $display_denied_reasons)
				$title = $this->locale->lang('login_access_denied');
			else
				$title = $this->locale->lang('loginform');
	
			$this->tpl->set_template('login.htm');
			$this->tpl->add_variables(array(
				'action_type' => BS_ACTION_LOGIN,
				'target_url' => $this->url->get_url('login'),
				'show_sendpw_link' => !BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE != 'disabled',
				'show_register_link' => $this->cfg['enable_registrations'] &&
					(!BS_ENABLE_EXPORT || BS_EXPORT_REGISTER_TYPE == 'link'),
				'register_url' => $register_url,
				'send_pw_url' => $send_pw_url,
				'title' => $title,
				'login_msg' => $login_msg,
				'denied_msg' => $denied
			));
			$this->tpl->restore_template();
		}
		// if the user is loggedin we simply set an error-message
		else
			$this->msgs->add_error($this->locale->lang('permission_denied'));
		
		$this->doc->set_base_template('login.htm');
	}
	
	/**
	 * Determines the search-keywords and returns them
	 *
	 * @return array an numeric array with the keywords
	 */
	public function get_search_keywords()
	{
		$hl = $this->input->get_var(BS_URL_HL,'get',PLIB_Input::STRING);
		if($hl !== null)
		{
			// undo the stuff of the input-class
			$hl = stripslashes(str_replace('&quot;','"',$hl));
			
			$temp = explode('"',$hl);
			$keywords = array();
			for($i = 0;$i < count($temp);$i++)
			{
				$temp[$i] = trim($temp[$i]);
				if($temp[$i] != '')
					$keywords[] = $temp[$i];
			}
			return $keywords;
		}
		
		return null;
	}
	
	/**
	 * builds the text for an "order-column"
	 *
	 * @param string $title the title of the column
	 * @param string $order_value the value of the order-parameter
	 * @param string $def_ascdesc the default value for BS_URL_AD (ASC or DESC)
	 * @param string $order the current value of BS_URL_ORDER
	 * @param string $url the current URL
	 * @return string the column-content
	 */
	public function get_order_column($title,$order_value,$def_ascdesc,$order,$url)
	{
		$preurl = $url.BS_URL_ORDER.'='.$order_value.'&amp;'.BS_URL_AD.'=';
		if($order == $order_value)
		{
			$asc_img = $this->user->get_theme_item_path('images/asc.gif');
			$desc_img = $this->user->get_theme_item_path('images/desc.gif');
			$result = $title.' <a href="'.$preurl.'ASC">';
			$result .= '<img src="'.$asc_img.'" alt="ASC" />';
			$result .= '</a> ';
			$result .= '<a href="'.$preurl.'DESC">';
			$result .= '<img src="'.$desc_img.'" alt="DESC" />';
			$result .= '</a>';
		}
		else
			$result = '<a href="'.$preurl.$def_ascdesc.'">'.$title.'</a>';
	
		return $result;
	}
	
	/**
	 * prepares the data for the output
	 *
	 * @return string the prepared data
	 */
	public function cache_basic_data()
	{
		$str = base64_decode(
			'cmV0dXJuICc8YSB0YXJnZXQ9Il9ibGFuayIgaHJlZj0iaHR0cDovL3d3dy5zY3JpcHQtc29sdXRpb24uZGUiPk'
		 .'JvYXJkc29sdXRpb24gdjEuNDAgQWxwaGExPC9hPiB8ICZjb3B5OyBOaWxzIEFzbXVzc2VuIDIwMDMtMjAwNyc7'
		);
		//$dstr = 'return \'<a target="_blank" href="http://www.script-solution.de">'.BS_VERSION.'</a> | &copy; Nils Asmussen 2003-2007\';';
		//echo base64_encode($dstr);
		return eval($str);
	}
	
	/**
	 * Determines the newest member
	 * 
	 * @return string the link to the member
	 */
	public function get_newest_member()
	{
		$nm = BS_DAO::get_profile()->get_newest_user();
		return BS_UserUtils::get_instance()->get_link($nm['id'],$nm['user_name'],$nm['user_group']);
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
		// points = 0 is a special case
		$ranks = $this->cache->get_cache('user_ranks');
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
		$result = '';
		
		if($is_mod)
		{
			$images = array(
				'is_mod' => true,
				'filled' => $this->cfg['mod_rank_filled_image'],
				'empty' => $this->cfg['mod_rank_empty_image']
			);
		}
		else
			$images = $this->auth->get_user_images($user_id,$group_ids);
		
		if($images['is_mod'])
			$title = $this->locale->lang('moderators');
		else
			$title = $this->auth->get_groupname((int)$group_ids);
		
		for($i = 0;$i < $rank_pos;$i++)
		{
			$result .= '<img alt="*" title="'.$title.'"';
			$result .= ' src="'.$this->user->get_theme_item_path($images['filled']).'" /> ';
		}
	
		for($i = 0;$i < ($ranknum - $rank_pos);$i++)
		{
			$result .= '<img alt="O" title="'.$title.'"';
			$result .= ' src="'.$this->user->get_theme_item_path($images['empty']).'" /> ';
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
				return PLIB_Path::inner().'images/filetypes/image.gif';
	
			case 'txt':
			case 'ini':
				return PLIB_Path::inner().'images/filetypes/text.gif';
	
			case 'pdf':
				return PLIB_Path::inner().'images/filetypes/pdf.gif';
	
			case 'htm':
			case 'html':
				return PLIB_Path::inner().'images/filetypes/html.gif';
	
			case 'zip':
			case 'rar':
			case 'tar':
			case 'gzip':
				return PLIB_Path::inner().'images/filetypes/archive.gif';
	
			case 'css':
				return PLIB_Path::inner().'images/filetypes/css.gif';
	
			case 'js':
				return PLIB_Path::inner().'images/filetypes/js.gif';
	
			default:
				return PLIB_Path::inner().'images/filetypes/unknown.gif';
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
		if($this->cfg['attachments_filetypes'] == '')
			return false;
	
		$extension = PLIB_FileUtils::get_extension($attachment);
		$types = explode('|',PLIB_String::strtolower($this->cfg['attachments_filetypes']));
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
		static $img_attachment = '';
		if(!$img_attachment)
			$img_attachment = $this->user->get_theme_item_path('images/attachment.gif');
		
		if($attachments > 0)
		{
			if($attachments == 1)
				$a_title = $attachments.' '.$this->locale->lang('attachment');
			else
				$a_title = $attachments.' '.$this->locale->lang('attachments');
			
			$prefix = '<img src="'.$img_attachment.'" alt="'.$a_title.'" title="'.$a_title.'" /> ';
		}
		else
			$prefix = '';
		
		return $prefix;
	}
	
	/**
	 * deletes the attachment with given path including the thumbnail
	 *
	 * @param string $path the path of the attachment (without PLIB_Path::inner())
	 */
	public function delete_attachment($path)
	{
		// delete the thumbnail, if it exists
		$ext = PLIB_FileUtils::get_extension($path,false);
		$start = PLIB_String::substr($path,0,PLIB_String::strlen($path) - PLIB_String::strlen($ext) - 1);
		if(is_file(PLIB_Path::inner().$start.'_thumb.'.$ext))
			@unlink(PLIB_Path::inner().$start.'_thumb.'.$ext);
	
		@unlink(PLIB_Path::inner().$path);
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
		static $images = null;
		if($images === null)
		{
			$images = array(
				'rate_back' => $this->user->get_theme_item_path('images/diagrams/rate_back.gif'),
				'rate_grey' => $this->user->get_theme_item_path('images/diagrams/rate_grey.gif'),
				'rate_red' => $this->user->get_theme_item_path('images/diagrams/rate_red.gif'),
				'rate_blue' => $this->user->get_theme_item_path('images/diagrams/rate_blue.gif')
			);
		}
		
		if($votes == 0)
			$result = 6;
		else
			$result = 5 - (@round($total / $votes,2) - 1);
		
		$pro = @round(100 / (5 / $result),0) * $multi;
		$text_ins = '( '.$this->locale->lang('school_grade').': '.abs($result - 6).' | ';
		$text_ins .= $votes.' '.$this->locale->lang('votes').' )';
		
		$img = ($votes == 0) ? 'grey' : 'red';
		if($votes == 0)
			$pro = 0;
	
		$res = '<img src="'.$images['rate_back'].'" alt="" />';
		$res .= '<img src="'.$images['rate_blue'].'" width="'.$pro.'" height="10"';
		$res .= ($text == 0) ? ' title="'.$text_ins.'" alt="'.$text_ins.'"' : '';
		$res .= ' /><img src="'.$images['rate_'.$img].'"';
		$res .= ' height="10" width="'.(($multi * 100) - $pro).'"';
		$res .= ($text == 0) ? ' title="'.$text_ins.'" alt="'.$text_ins.'"' : '';
		$res .= ' /><img src="'.$images['rate_back'].'" alt="" /> ( ';
		$res .= ($text == 1) ? $this->locale->lang('school_grade').': ' : ' ';
		$res .= abs($result - 6).' | '.$votes.' ';
		$res .= ($text == 1) ? $this->locale->lang('votes') : '';
		$res .= ' )';
		
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
	 * @return PLIB_Email_Base the email-class
	 */
	public function get_mailer($receiver = '',$subject = '',$message = '')
	{
		// use php-mail()?
		if($this->cfg['mail_method'] == 'mail')
			$c = new PLIB_Email_PHP($receiver,$subject,$message);
		else
			$c = new PLIB_Email_SMTP($receiver,$subject,$message);

		// set basic properties for BS
		$c->set_xmailer(BS_VERSION);
		$c->set_from($this->cfg['board_email']);
		$c->set_charset(BS_HTML_CHARSET);
		
		// set SMTP-properties
		if($this->cfg['mail_method'] != 'mail')
		{
			$c->set_smtp_host($this->cfg['smtp_host']);
			$c->set_smtp_port($this->cfg['smtp_port']);
			$c->set_smtp_login($this->cfg['smtp_login']);
			$c->set_smtp_password($this->cfg['smtp_password']);
		}
		
		return $c;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>