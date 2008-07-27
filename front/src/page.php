<?php
/**
 * Contains the page-class which is used to display the whole frontend of the board except the
 * popups and so on.
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Represents the frontend of Boardsolution. Includes all necessary files and loads the appropriate
 * module. And it shows header, the module and footer.
 *
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Page extends BS_Page
{
	/**
	 * The module
	 *
	 * @var BS_Front_Module
	 */
	private $_module;
	
	/**
	 * The name of the current module
	 *
	 * @var string
	 */
	private $_module_name;
	
	/**
	 * Wether the current user has access to this page
	 *
	 * @var boolean
	 */
	private $_has_access = true;
	
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
		try
		{
			parent::__construct();
			
			$this->_check_addfields();
			
			$this->_module = $this->_get_module();
		}
		catch(PLIB_Exceptions_Critical $e)
		{
			echo $e;
		}
	}

	/**
	 * @return boolean true if the current user has access to this page
	 */
	public final function has_access()
	{
		return $this->_has_access;
	}
	
	/**
	 * Sets wether the current user has access to this page
	 *
	 * @param boolean $has_access the new value
	 */
	public final function set_has_access($has_access)
	{
		$this->_has_access = (bool)$has_access;
	}

	/**
	 * @return boolean true if the headline will be shown
	 */
	public final function is_headline_shown()
	{
		return $this->_show_headline;
	}
	
	/**
	 * Sets wether the headline should be shown
	 *
	 * @param boolean $show the new value
	 */
	public final function set_show_headline($show)
	{
		$this->_show_headline = (bool)$show;
	}

	/**
	 * @return boolean true if the bottom will be shown
	 */
	public final function is_bottom_shown()
	{
		return $this->_show_bottom;
	}
	
	/**
	 * Sets wether the bottom should be shown
	 *
	 * @param boolean $show the new value
	 */
	public final function set_show_bottom($show)
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
	public final function get_robots_value()
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
	public final function set_robots_value($robots)
	{
		$this->_robots_value = (string)$robots;
	}
	
	/**
	 * @return string the name of the module that is used
	 */
	public final function get_module_name()
	{
		return $this->_module_name;
	}
	
	/**
	 * @see BS_Page::before_start()
	 */
	protected function before_start()
	{
		parent::before_start();
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();
		
		// add the home-breadcrumb
		$this->add_breadcrumb($locale->lang('home'),$url->get_forums_url());
		
		$this->_action_perf->set_prefix('BS_Front_Action_');
		
		// init the module
		$this->_module->init($this);
		
		// set the default template if not already done
		$template = '';
		if($this->get_template() === null)
		{
			$classname = get_class($this->_module);
			$prefixlen = PLIB_String::strlen('BS_Front_Module_');
			$template = PLIB_String::strtolower(PLIB_String::substr($classname,$prefixlen)).'.htm';
			$this->set_template($template);
		}
		
		if($this->is_output_enabled())
			$user->set_location();
	}

	/**
	 * @see PLIB_Page::before_render()
	 */
	protected final function before_render()
	{
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$msgs = PLIB_Props::get()->msgs();
		$user = PLIB_Props::get()->user();
		
		// add redirect information
		$redirect = $this->get_redirect();
		if($redirect)
			$tpl->add_array('redirect',$redirect,'inc_header.htm');
		
		// notify the template if an error has occurred
		$tpl->add_global('module_error',$this->error_occurred());
		
		$tpl->add_global('gisloggedin',$user->is_loggedin());
		$tpl->add_global('gusername',$user->get_user_name());
		$tpl->add_global('guserid',$user->get_user_id());
		$tpl->add_global('gisadmin',$user->is_admin());
		$tpl->add_global('glang',$user->get_language());
		// TODO add theme
		// TODO add current module
		
		// add messages
		$msgs->add_messages();
		
		$this->set_gzip($cfg['enable_gzip']);
	}

	/**
	 * @see PLIB_Page::content()
	 */
	protected final function content()
	{
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$msgs = PLIB_Props::get()->msgs();
		$functions = PLIB_Props::get()->functions();
		$auth = PLIB_Props::get()->auth();

		// check this here (before the action will be performed)
		$board_access = $auth->has_board_access() || $this->_module->is_guest_only();
		$module_access = $this->has_access();
		
		if($this->get_action_result() < 1)
		{
			// the modules register, sendpw, change_password and resend_activation are always allowed for
			// guests
			if($board_access)
			{
				// board deactivated?
				if($this->_module_name != 'login' && $cfg['enable_board'] == 0 &&
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
				{
					$template = $this->get_template();
					
					// run the module
					$tpl = PLIB_Props::get()->tpl();
					$tpl->set_template($template);
					
					$this->_module->run();
					
					$tpl->restore_template();
					
					// if errors have occurred and the output is disabled we want to display them
					if(!$this->is_output_enabled() && $this->error_occurred())
					{
						// remove everything from the output-buffer
						ob_clean();
						$this->set_output_enabled(true);
						$this->_header();
						if($this->get_template() == $template)
							$this->set_template('error.htm');
					}
				}
			}
		}
	}

	/**
	 * @see PLIB_Page::header()
	 */
	protected final function header()
	{
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

		// add actions of the current module
		$this->_action_perf->add_actions($this->_module_name,$this->get_actions());
		
		// perform actions
		$this->perform_actions();
		
		$this->_header();
	}
	
	/**
	 * Initializes the header-templates
	 */
	private function _header()
	{
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$user = PLIB_Props::get()->user();
		$input = PLIB_Props::get()->input();
		$auth = PLIB_Props::get()->auth();
		$unread = PLIB_Props::get()->unread();
		
		$action_result = $this->get_action_result();
		$tpl->add_global('action_result',$action_result);
		$tpl->add_global('module_error',false);

		$breadcrumbs = PLIB_Helper::generate_location($this);
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
			'charset' => 'charset='.$this->get_charset(),
			'mimetype' => $this->get_mimetype(),
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
	protected final function footer()
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
	 * Determines the module to load and returns it
	 *
	 * @return BS_Front_Module the module
	 */
	private function _get_module()
	{
		$cfg = PLIB_Props::get()->cfg();
		$user = PLIB_Props::get()->user();

		// determine start-module
		if($cfg['enable_portal'] == 1 &&
			($user->is_loggedin() || $user->get_profile_val('startmodule' == 'portal')))
			$default = 'portal';
		else
			$default = 'forums';
		
		$this->_module_name = PLIB_Helper::get_module_name(
			'BS_Front_Module_',BS_URL_ACTION,$default,'front/module/'
		);
		$class = 'BS_Front_Module_'.$this->_module_name;
		return new $class();
	}
	
	/**
	 * Checks wether any required additional field is empty. If so the user will be redirected
	 * to the profile-info-page (if he/she is not already there).
	 */
	private function _check_addfields()
	{
		$cfg = PLIB_Props::get()->cfg();
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		$url = PLIB_Props::get()->url();

		if($cfg['force_fill_of_empty_req_fields'] == 1)
		{
			$action = $input->get_var(BS_URL_ACTION,'get',PLIB_Input::STRING);
			$loc = $input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
			if($user->is_loggedin() && ($action != 'userprofile' || $loc != 'infos'))
			{
				if(BS_AddField_Manager::get_instance()->is_any_required_field_empty())
				{
					$murl = $url->get_url('userprofile','&'.BS_URL_LOC.'=infos&'.BS_URL_MODE.'=1','&');
					$this->redirect($murl);
				}
			}
		}
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