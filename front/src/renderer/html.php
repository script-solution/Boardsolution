<?php
/**
 * Contains the frontend-html-renderer-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.renderer
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The html-renderer for the boardsolution-frontend
 *
 * @package			Boardsolution
 * @subpackage	front.src.renderer
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Renderer_HTML extends PLIB_Document_Renderer_HTML_Default
{
	/**
	 * Wether this page is viewable by guests only
	 *
	 * @var boolean
	 */
	private $_guest_only = false;
	
	/**
	 * Wether the headline should be displayed
	 *
	 * @var boolean
	 */
	private $_show_headline = true;
	
	/**
	 * Wether the bottom-line should be displayed
	 *
	 * @var boolean
	 */
	private $_show_bottom = true;
	
	/**
	 * The value for the meta-tag "robots".
	 *
	 * @var string
	 */
	private $_robots_value = 'noindex,nofollow';
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();
		
		// always set the location in html-pages
		$user->set_location();
		
		// add the home-breadcrumb
		$this->add_breadcrumb($locale->lang('home'),$url->get_forums_url());
		
		$this->_action_perf->set_prefix('BS_Front_Action_');
	}

	/**
	 * @return boolean true if the headline will be shown
	 */
	public function is_headline_shown()
	{
		return $this->_show_headline;
	}
	
	/**
	 * Sets wether the headline should be shown
	 *
	 * @param boolean $show the new value
	 */
	public function set_show_headline($show)
	{
		$this->_show_headline = (bool)$show;
	}

	/**
	 * @return boolean true if the bottom will be shown
	 */
	public function is_bottom_shown()
	{
		return $this->_show_bottom;
	}
	
	/**
	 * Sets wether the bottom should be shown
	 *
	 * @param boolean $show the new value
	 */
	public function set_show_bottom($show)
	{
		$this->_show_bottom = (bool)$show;
	}
	
	/**
	 * Returns the value for the meta-tag "robots".
	 * That means if you for example return "noindex" the meta-tag would look like:
	 * <code>
	 * 	<meta name="robots" content="noindex" />
	 * </code>
	 * By default the value is "noindex,nofollow".
	 * 
	 * @return string the value for the meta-tag "follow"
	 */
	public function get_robots_value()
	{
		return $this->_robots_value;
	}
	
	/**
	 * Sets the value for the meta-tag "robots".
	 * That means if you for example return "noindex" the meta-tag would look like:
	 * <code>
	 * 	<meta name="robots" content="noindex" />
	 * </code>
	 * By default the value is "noindex,nofollow".
	 *
	 * @param string $robots the new value
	 */
	public function set_robots_value($robots)
	{
		$this->_robots_value = (string)$robots;
	}

	/**
	 * @see PLIB_Document_Renderer_HTML_Default::load_action_perf()
	 *
	 * @return PLIB_Actions_Performer
	 */
	protected function load_action_perf()
	{
		return new BS_Front_Action_Performer();
	}
	
	/**
	 * @see BS_Page::before_start()
	 */
	protected function before_start()
	{
		parent::before_start();
		
		$doc = PLIB_Props::get()->doc();
		
		// set the default template if not already done
		$template = '';
		if($this->get_template() === null)
		{
			$classname = get_class($doc->get_module());
			$prefixlen = PLIB_String::strlen('BS_Front_Module_');
			$template = PLIB_String::strtolower(PLIB_String::substr($classname,$prefixlen)).'.htm';
			$this->set_template($template);
		}
	}

	/**
	 * @see PLIB_Page::before_render()
	 */
	protected function before_render()
	{
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$user = PLIB_Props::get()->user();
		$doc = PLIB_Props::get()->doc();
		
		// add redirect information
		$redirect = $doc->get_redirect();
		if($redirect)
			$tpl->add_array('redirect',$redirect,'inc_header.htm');
		
		// notify the template if an error has occurred
		$tpl->add_global('module_error',$doc->get_module()->error_occurred());
		
		$action_result = $this->get_action_result();
		$tpl->add_global('action_result',$action_result);
		
		$tpl->add_global('gisloggedin',$user->is_loggedin());
		$tpl->add_global('gusername',$user->get_user_name());
		$tpl->add_global('guserid',$user->get_user_id());
		$tpl->add_global('gisadmin',$user->is_admin());
		$tpl->add_global('glang',$user->get_language());
		// TODO add theme
		// TODO add current module
		
		// handle messages
		$msgs = PLIB_Props::get()->msgs();
		if($msgs->contains_msg())
			$this->_handle_msgs($msgs);
		
		$doc->set_gzip($cfg['enable_gzip']);
	}

	/**
	 * Handles the collected messages
	 *
	 * @param PLIB_Document_Messages $msgs
	 */
	private function _handle_msgs($msgs)
	{
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		
		if($msgs->contains_no_access())
			$functions->show_login_form();
		else
		{
			$amsgs = $msgs->get_all_messages();
			$links = $msgs->get_links();
			$tpl->set_template('inc_messages.htm');
			$tpl->add_array('errors',$amsgs[PLIB_Document_Messages::ERROR]);
			$tpl->add_array('warnings',$amsgs[PLIB_Document_Messages::WARNING]);
			$tpl->add_array('notices',$amsgs[PLIB_Document_Messages::NOTICE]);
			$tpl->add_array('links',$links);
			$tpl->add_variables(array(
				'title' => $locale->lang('information'),
				'messages' => $msgs->contains_error() || $msgs->contains_notice() || $msgs->contains_warning()
			));
			$tpl->restore_template();
		}
	}

	/**
	 * @see PLIB_Page::content()
	 */
	protected function content()
	{
		$cfg = PLIB_Props::get()->cfg();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$msgs = PLIB_Props::get()->msgs();
		$functions = PLIB_Props::get()->functions();
		$auth = PLIB_Props::get()->auth();
		$doc = PLIB_Props::get()->doc();

		$module = $doc->get_module();
		
		// check this here (before the action will be performed)
		$board_access = $auth->has_board_access() || $module->is_guest_only();
		$module_access = $this->has_access();
		
		if($this->get_action_result() < 1)
		{
			// the modules register, sendpw, change_password and resend_activation are always allowed for
			// guests
			if($board_access)
			{
				// board deactivated?
				if($doc->get_module_name() != 'login' && $cfg['enable_board'] == 0 &&
					!$user->is_admin())
				{
					$msg = nl2br(PLIB_StringHelper::htmlspecialchars_back($cfg['board_disabled_text']));
					$msg .= $locale->lang('board_deactivated_notice');
					$msgs->add_notice($msg);
					$this->set_error();
				}
				// user banned?
				else if($functions->is_banned('ip',$user->get_user_ip()))
				{
					$msgs->add_notice($locale->lang('ip_banned'));
					$this->set_error();
				}
				else if(!$module_access)
				{
					$functions->show_login_form();
					$this->set_error();
				}
				else
					parent::content();
			}
		}
	}

	/**
	 * @see PLIB_Page::header()
	 */
	protected function header()
	{
		$doc = PLIB_Props::get()->doc();
		
		// add module-independend actions
		$actions = array(
			BS_ACTION_LOGOUT => 'logout',
			BS_ACTION_LOGIN => 'login',
			BS_ACTION_CHANGE_READ_STATUS => 'chg_read_status'
		);
		foreach($actions as $id => $action)
		{
			include_once(PLIB_Path::server_app().'front/src/action/'.$action.'.php');
			$classname = 'BS_Front_Action_'.$action;
			$a = new $classname($id);
			$this->_action_perf->add_action($a);
		}
		
		// perform actions
		$this->perform_actions();
		
		
		// prepare header-templates
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$user = PLIB_Props::get()->user();
		$input = PLIB_Props::get()->input();
		$auth = PLIB_Props::get()->auth();
		$unread = PLIB_Props::get()->unread();

		$breadcrumbs = $this->get_breadcrumbs();
		$page_title = str_replace($locale->lang('home'),$cfg['forum_title'],$breadcrumbs);
		$page_title = strip_tags($page_title);
		$this->set_title($page_title);

		// show page header
		$tpl->set_template('inc_header.htm');
		$tpl->add_variables(array(
			'cookie_path' => $cfg['cookie_path'],
			'cookie_domain' => $cfg['cookie_domain'],
			'theme' => $user->get_theme(),
			'title' => $this->get_title(),
			'charset' => 'charset='.$doc->get_charset(),
			'mimetype' => $doc->get_mimetype(),
			'cssfiles' => $this->get_css_files(),
			'cssblocks' => $this->get_css_blocks(),
			'jsfiles' => $this->get_js_files(),
			'jsblocks' => $this->get_js_blocks(),
			'action' => $input->get_var(BS_URL_ACTION,'get',PLIB_Input::STRING),
			'robots_value' => $this->get_robots_value(),
			'rss20_feed' => $url->get_url('news_feed','&amp;'.BS_URL_MODE.'=rss20'),
			'atom_feed' => $url->get_url('news_feed','&amp;'.BS_URL_MODE.'=atom'),
			'sig_max_height' => $cfg['sig_max_height'],
			'show_headline' => $this->_show_headline
		));
		$tpl->restore_template();

		// show headline
		if($this->_show_headline)
		{
			$tpl->set_template('inc_headline.htm');
			$tpl->add_variables(array(
				'location' => $breadcrumbs,
				'action_type' => BS_ACTION_LOGIN,
				'login_url' => $url->get_url('login'),
				'show_deactivated_notice' => $cfg['enable_board'] == 0 && $user->is_admin(),
				'headline_url' => $url->get_forums_url()
			));
			
			$top_links = array();
		
			// generate the buttons
			if($auth->has_acp_access())
			{
				$top_links[] = array(
					'title' => $locale->lang('adminarea'),
					'text' => $locale->lang('adminarea'),
					'url' => $url->get_admin_url()
				);
			}
			if($this->_show_top_link('enable_memberlist','view_memberlist'))
			{
				$top_links[] = array(
					'title' => $locale->lang('memberlist_desc'),
					'text' => $locale->lang('memberlist'),
					'url' => $url->get_url('memberlist')
				);
			}
			if($this->_show_top_link('enable_linklist','view_linklist'))
			{
				$top_links[] = array(
					'title' => $locale->lang('linklist_desc'),
					'text' => $locale->lang('linklist'),
					'url' => $url->get_url('linklist')
				);
			}
			if($this->_show_top_link('enable_stats','view_stats'))
			{
				$top_links[] = array(
					'title' => $locale->lang('statistics_desc'),
					'text' => $locale->lang('statistics'),
					'url' => $url->get_url('stats')
				);
			}
			if($this->_show_top_link('enable_faq'))
			{
				$top_links[] = array(
					'title' => $locale->lang('faq_desc'),
					'text' => $locale->lang('faq'),
					'url' => $url->get_url('faq')
				);
			}
		
			if(!$user->is_loggedin())
			{
				if($cfg['enable_registrations'] && (!BS_ENABLE_EXPORT || BS_EXPORT_REGISTER_TYPE == 'link'))
				{
					$top_links[] = array(
						'title' => $locale->lang('register'),
						'text' => $locale->lang('register'),
						'url' => !BS_ENABLE_EXPORT ? $url->get_url('register') : BS_EXPORT_REGISTER_LINK
					);
				}
			}
			else
			{
				$top_links[] = array(
					'title' => $locale->lang('yourprofile'),
					'text' => $locale->lang('profile'),
					'url' => $url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pr_infos')
				);
			}
		
			if($this->_show_top_link('enable_calendar','view_calendar'))
			{
				$top_links[] = array(
					'title' => $locale->lang('calendar_desc'),
					'text' => $locale->lang('calendar'),
					'url' => $url->get_url('calendar')
				);
			}
		
			if($this->_show_top_link('enable_search','view_search'))
			{
				$top_links[] = array(
					'title' => $locale->lang('search_desc'),
					'text' => $locale->lang('search'),
					'url' => $url->get_url('search')
				);
			}
		
			$tpl->add_array('top_links',$top_links);
		
			$username = '';
			$sendpw_url = '';
			$resend_url = '';
			$unread_news_title = '';
			if($user->is_loggedin())
			{
				$username = BS_UserUtils::get_instance()->get_link(
					$user->get_user_id(),$user->get_profile_val('user_name'),
					$user->get_profile_val('user_group')
		  	);
		  	
				$news_num = $unread->get_unread_news_num();
				$unread_news_title = $locale->lang('portal').' ( ';
				$unread_news_title .= sprintf($locale->lang('unread_news'),$news_num).' )';
			}
			else
			{
				$username = $locale->lang('guest');
				if(!BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE != 'disabled')
				{
					if(!BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE == 'enabled')
						$sendpw_url = $url->get_url('sendpw');
					else
						$sendpw_url = BS_EXPORT_SEND_PW_LINK;
				}
				
				if(!BS_ENABLE_EXPORT || BS_EXPORT_RESEND_ACT_TYPE == 'link')
				{
					if(!BS_ENABLE_EXPORT)
						$resend_url = $url->get_url('resend_activation');
					else
						$resend_url = BS_EXPORT_RESEND_ACT_LINK;
				}
			}
			
			$lastlogin = false;
			if($user->is_loggedin())
			{
				if($ll = $input->get_var(BS_COOKIE_PREFIX.'lastlogin','cookie',PLIB_Input::INTEGER))
					$lastlogin = PLIB_Date::get_date($ll);
			}
			
			$cdate = new PLIB_Date();
			$tpl->add_variables(array(
				'enable_portal' => $cfg['enable_portal'] == 1,
				'unread_news_title' => $unread_news_title,
				'enable_pms' => $user->is_loggedin() && $cfg['enable_pms'] == 1 &&
					$user->get_profile_val('allow_pms') == 1,
				'unread_topic_count' => $unread->get_length(),
				'unread_pm_count' => $user->is_loggedin() ? $user->get_profile_val('unread_pms') : 0,
				'unread_news_count' => $unread->get_unread_news_num(),
				'username' => $username,
				'forgotpw_link' => $sendpw_url,
				'resendact_link' => $resend_url,
				'current_date' => $cdate->to_format('longdate',true),
				'current_time' => $cdate->to_format('time_s'),
				'lastlogin' => $lastlogin
			));
			$tpl->restore_template();
		}
	}

	/**
	 * @see PLIB_Page::footer()
	 */
	protected function footer()
	{
		$cfg = PLIB_Props::get()->cfg();
		$locale = PLIB_Props::get()->locale();
		$user = PLIB_Props::get()->user();
		$auth = PLIB_Props::get()->auth();
		$db = PLIB_Props::get()->db();
		$tpl = PLIB_Props::get()->tpl();
		$functions = PLIB_Props::get()->functions();
		$forums = PLIB_Props::get()->forums();

		if($this->_show_bottom)
		{
			$options = array();
			if($cfg['enable_portal'] == 1)
				$options['portal'] = $locale->lang('portal');
			if($user->is_admin())
				$options['admin'] = $locale->lang('adminarea');
			if(!$user->is_loggedin() && $cfg['enable_registrations'] &&
					(!BS_ENABLE_EXPORT || BS_EXPORT_REGISTER_TYPE == 'link'))
				$options['register'] = $locale->lang('register');
			else if($user->is_loggedin())
				$options['profile'] = $locale->lang('profile');
			if($cfg['enable_memberlist'] == 1)
				$options['memberlist'] = $locale->lang('memberlist');
			if($cfg['enable_stats'] == 1)
				$options['stats'] = $locale->lang('statistics');
			if($cfg['enable_calendar'] == 1)
				$options['calendar'] = $locale->lang('calendar');
			if($cfg['enable_search'] == 1)
				$options['search'] = $locale->lang('search');
			if($cfg['enable_linklist'] == 1)
				$options['linklist'] = $locale->lang('linklist');
			if($cfg['enable_faq'] == 1)
				$options['faq'] = $locale->lang('faq');
			if($user->is_loggedin() && $cfg['enable_pms'] == 1)
				$options['pms'] = $locale->lang('privatemessages');
			if($user->is_loggedin())
				$options['unread'] = $locale->lang('unread_threads');
			$options['team'] = $locale->lang('the_team');
			if($auth->has_global_permission('view_online_locations'))
				$options['userloc'] = $locale->lang('user_locations');
		
			$nodes = array();
			$forum_data = $forums->get_all_nodes();
			$forum_num = count($forum_data);
			for($i = 0;$i < $forum_num;$i++)
			{
				$fnode = $forum_data[$i];
				$fdata = $fnode->get_data();
				
				// display forum?
				if($cfg['hide_denied_forums'] == 1 &&
					 !$auth->has_access_to_intern_forum($fdata->get_id()))
					continue;
		
				$forum_name = '';
				for($x = 0;$x < $fnode->get_layer();$x++)
					$forum_name .= ' --';
				$forum_name .= ' '.$fdata->get_name();
		
				$nodes[] = array(
					'forum_id' => $fdata->get_id(),
					'forum_name' => $forum_name
				);
			}
			
			$debug = '&nbsp;';
			if(BS_DEBUG > 0)
			{
				$profiler = PLIB_Props::get()->profiler();
				$qry_num = $db->get_performed_query_num();
				$debug = $profiler->get_time().' '.$locale->lang('sec').', '
					.$qry_num.' '.$locale->lang('qrys').', '
					.PLIB_StringHelper::get_formated_data_size($profiler->get_memory_usage());
			}
			
			$tpl->set_template('inc_bottom.htm');
			$tpl->add_variables(array(
				'insert_time' => $debug,
				'forums_init' => $functions->cache_basic_data(),
				'options' => $options
			));
		
			$tpl->add_array('forums',$nodes);
			$tpl->restore_template();
		}
				
		// show footer
		$tpl->set_template('inc_footer.htm');
		$tpl->add_variables(array(
			'queries' => BS_DEBUG == 2 ? PLIB_PrintUtils::to_string($db->get_performed_queries()) : '',
			'show_bottom' => $this->_show_bottom
		));
		$tpl->restore_template();
	}
	
	/**
	 * determines wether to show a top-link
	 *
	 * @param string $cfg_entry the config-entry which controls if the module is activated
	 * @param string $auth_entry the auth-entry which controls if the user is allowed to view the module
	 * @return boolean true if the link should be displayed
	 */
	private function _show_top_link($cfg_entry,$auth_entry = '')
	{
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();

		$display = true;
		if($cfg[$cfg_entry] == 0)
			$display = false;
	
		if($auth_entry != '' && !$cfg['display_denied_options'] &&
			 !$auth->has_global_permission($auth_entry))
		{
			$display = false;
		}
	
		return $display;
	}
	
	protected function get_print_vars()
	{
		return array_merge(parent::get_print_vars(),get_object_vars($this));
	}
}
?>