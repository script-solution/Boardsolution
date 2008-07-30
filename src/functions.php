<?php
/**
 * Contains the general functions
 * 
 * @version			$Id$
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
final class BS_Functions extends FWS_Object
{
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
	 * Builds the start-url for the current user
	 * 
	 * @return string the start-url
	 */
	public function get_start_url()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		if($cfg['enable_portal'] == 1 &&
			($user->is_loggedin() || $user->get_profile_val('startmodule') == 'portal'))
			return BS_URL::get_portal_url();
		
		return BS_URL::get_forums_url();
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
	 * Generates the pagination from the given object
	 *
	 * @param FWS_Pagination $pagination the FWS_Pagination-object
	 * @param string $url the URL containing {d} at the position where to put the page-number
	 * @return string the result
	 */
	public function add_pagination($pagination,$url)
	{
		$cfg = FWS_Props::get()->cfg();
		$tpl = FWS_Props::get()->tpl();

		if(!($pagination instanceof FWS_Pagination))
			FWS_Helper::def_error('instance','pagination','FWS_Pagination',$pagination);
		
		if(empty($url))
			FWS_Helper::def_error('empty','url',$url);
		
		if($cfg['show_always_page_split'] == 1 || $pagination->get_page_count() > 1)
		{
			$repl = urlencode('{d}');
			
			$page = $pagination->get_page();
			$numbers = $pagination->get_page_numbers();
			$tnumbers = array();
			foreach($numbers as $n)
			{
				$number = $n;
				$link = '';
				if(FWS_Helper::is_integer($n))
					$link = str_replace($repl,$n,$url);
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
			
			$tpl->set_template('inc_page_split.htm');
			$tpl->add_array('numbers',$tnumbers);
			$tpl->add_variables(array(
				'page' => $page,
				'total_pages' => $pagination->get_page_count(),
				'start_item' => $start_item,
				'end_item' => $end_item,
				'total_items' => $pagination->get_num(),
				'prev_url' => str_replace($repl,$page - 1,$url),
				'next_url' => str_replace($repl,$page + 1,$url),
				'first_url' => str_replace($repl,1,$url),
				'last_url' => str_replace($repl,$pagination->get_page_count(),$url)
			));
			$tpl->restore_template();
		}
	}
	
	/**
	 * A small version of the pagination
	 *
	 * @param FWS_Pagination $pagination the FWS_Pagination-object
	 * @param string $link the URL containing {d} at the position where to put the page-number
	 * @return string the pagination
	 */
	public function get_pagination_small($pagination,$link)
	{
		$res = '';
		$page = $pagination->get_page();
		$numbers = $pagination->get_page_numbers();
		$repl = urlencode('{d}');
		foreach($numbers as $n)
		{
			if(FWS_Helper::is_integer($n))
			{
				if($n == $page)
					$res .= $n.' ';
				else
					$res .= '<a href="'.str_replace($repl,$n,$link).'">'.$n.'</a> ';
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
		$locale = FWS_Props::get()->locale();

		$result = '';
		if($total_pages > 1)
		{
			$repl = urlencode('{d}');
			$result = '[ '.$locale->lang('pages').': ';
			for($i = 1;$i <= $total_pages;$i++)
			{
				if($i < 5)
					$result .= '<a href="'.str_replace($repl,$i,$link).'">'.$i.'</a> ';
			}
	
			if($total_pages > 5)
				$result .= ' ... ';
	
			if($total_pages > 4)
			{
				$result .= '<a href="'.str_replace($repl,$total_pages,$link).'">';
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
	 * @param string name the name (without BS_COOKIE_PREFIX)
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
	 * Signalizes the document that the login form should be displayed (instead of the module)
	 * The denied-message will be displayed if the user is loggedin or a login-form
	 * if the user is loggedout
	 *
	 * @param boolean $display_denied_reasons do you want to display the denied-reasons? (default=true)
	 * @return string the login-form
	 */
	public function show_login_form($display_denied_reasons = true)
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$msgs = FWS_Props::get()->msgs();
		$doc = FWS_Props::get()->doc();

		if(!$user->is_loggedin())
		{
			if($cfg['enable_registrations'] && !BS_ENABLE_EXPORT)
				$register_url = BS_URL::get_url('register');
			else if($cfg['enable_registrations'] && BS_EXPORT_REGISTER_TYPE == 'link')
				$register_url = BS_EXPORT_REGISTER_LINK;
			else
				$register_url = '';
	
			if(!BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE == 'enabled')
				$send_pw_url = BS_URL::get_url('sendpw');
			else if(BS_EXPORT_SEND_PW_TYPE == 'link')
				$send_pw_url = BS_EXPORT_SEND_PW_LINK;
			else
				$send_pw_url = '';
			
			if(!BS_ENABLE_EXPORT)
				$resend_act_link_url = BS_URL::get_url('resend_activation');
			else if(BS_EXPORT_RESEND_ACT_TYPE == 'link')
				$resend_act_link_url = BS_EXPORT_RESEND_ACT_LINK;
			else
				$resend_act_link_url = '';
	
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
						if($cfg['enable_registrations'] &&
							(!BS_ENABLE_EXPORT || BS_EXPORT_REGISTER_TYPE == 'link'))
						{
							$login_msg .= sprintf(
								' '.$locale->lang('login_message_'.$type),
								'<a href="'.$register_url.'">'.$locale->lang('here').'</a>'
							);
						}
						break;
	
					case 'forgot_pw':
						if(!BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE != 'disabled')
							$login_msg .= $locale->lang('login_message_'.$type);
						break;
	
					case 'activate':
						if(!BS_ENABLE_EXPORT || BS_EXPORT_RESEND_ACT_TYPE == 'link')
						{
							if($cfg['account_activation'] == 'email')
							{
								$login_msg .= sprintf(
									$locale->lang('login_message_'.$type),
									'<a href="'.$resend_act_link_url.'">'
										.$locale->lang('here').'</a>'
								);
							}
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
	
			$tpl->set_template('login.htm');
			$tpl->add_variables(array(
				'action_type' => BS_ACTION_LOGIN,
				'target_url' => BS_URL::get_url('login'),
				'show_sendpw_link' => !BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE != 'disabled',
				'show_register_link' => $cfg['enable_registrations'] &&
					(!BS_ENABLE_EXPORT || BS_EXPORT_REGISTER_TYPE == 'link'),
				'register_url' => $register_url,
				'send_pw_url' => $send_pw_url,
				'title' => $title,
				'login_msg' => $login_msg,
				'denied_msg' => $denied
			));
			$tpl->restore_template();
		}
		// if the user is loggedin we simply set an error-message
		else
			$msgs->add_error($locale->lang('permission_denied'));
		
		// TODO how to do that?
		if($doc->get_renderer() instanceof BS_Front_Renderer_HTML)
			$doc->get_renderer()->set_template('login.htm');
	}
	
	/**
	 * Determines the search-keywords and returns them
	 *
	 * @return array an numeric array with the keywords
	 */
	public function get_search_keywords()
	{
		$input = FWS_Props::get()->input();

		$hl = $input->get_var(BS_URL_HL,'get',FWS_Input::STRING);
		if($hl !== null)
		{
			// undo the stuff of the input-class
			$hl = stripslashes(str_replace('&quot;','"',$hl));
			// backslashes are not supported here
			$hl = str_replace('\\','',$hl);
			
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
		$user = FWS_Props::get()->user();

		$preurl = $url.BS_URL_ORDER.'='.$order_value.'&amp;'.BS_URL_AD.'=';
		if($order == $order_value)
		{
			$asc_img = $user->get_theme_item_path('images/asc.gif');
			$desc_img = $user->get_theme_item_path('images/desc.gif');
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
		
		for($i = 0;$i < $rank_pos;$i++)
		{
			$result .= '<img alt="*" title="'.$title.'"';
			$result .= ' src="'.$user->get_theme_item_path($images['filled']).'" /> ';
		}
	
		for($i = 0;$i < ($ranknum - $rank_pos);$i++)
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
		
		$pro = @round(100 / (5 / $result),0) * $multi;
		$text_ins = '( '.$locale->lang('school_grade').': '.abs($result - 6).' | ';
		$text_ins .= $votes.' '.$locale->lang('votes').' )';
		
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
		$res .= ($text == 1) ? $locale->lang('school_grade').': ' : ' ';
		$res .= abs($result - 6).' | '.$votes.' ';
		$res .= ($text == 1) ? $locale->lang('votes') : '';
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
	 * returns the search-ignore words
	 *
	 * @return array an associative array with all words to ignore:
	 * 	<code>
	 * 		array(<word> => true)
	 * 	</code>
	 */
	public function get_search_ignore_words()
	{
		$functions = FWS_Props::get()->functions();

		// we use the default-forum-language, because we guess that most of the posts will be in
		// this language
		$lang = $functions->get_def_lang_folder();
		$file = FWS_Path::server_app().'language/'.$lang.'/search_words.txt';
	
		if(!file_exists($file))
			return array();
	
		$words = array();
		$lines = file($file);
		foreach($lines as $l)
		{
			$line = trim($l);
			if($line != '')
				$words[$line] = true;
		}
	
		return $words;
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>