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
final class BS_Front_Renderer_HTML extends FWS_Document_Renderer_HTML_Default
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
	 * Wether the login-form should be displayed instead of the module
	 *
	 * @var boolean
	 */
	private $_show_login = false;
	
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
		
		$this->set_action_performer(new BS_Front_Action_Performer());
		
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		
		// always set the location in html-pages
		$user->set_location();
		
		// add the home-breadcrumb
		$this->add_breadcrumb($locale->lang('home'),BS_URL::build_forums_url());
		
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
	 * @see FWS_Document_Renderer_HTML_Default::before_start()
	 */
	protected function before_start()
	{
		parent::before_start();
		
		$doc = FWS_Props::get()->doc();
		
		// set the default template if not already done
		$template = '';
		if($this->get_template() === null)
		{
			$classname = get_class($doc->get_module());
			$prefixlen = FWS_String::strlen('BS_Front_Module_');
			$template = FWS_String::strtolower(FWS_String::substr($classname,$prefixlen)).'.htm';
			$this->set_template($template);
		}
	}

	/**
	 * @see FWS_Document_Renderer_HTML_Default::before_render()
	 */
	protected function before_render()
	{
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$doc = FWS_Props::get()->doc();
		
		// add redirect information
		$redirect = $doc->get_redirect();
		if($redirect)
			$tpl->add_variable_ref('redirect',$redirect,'inc_header.htm');
		
		// notify the template if an error has occurred
		$tpl->add_global('module_error',$doc->get_module()->error_occurred());
		
		$action_result = $this->get_action_result();
		$tpl->add_global('action_result',$action_result);
		
		// handle messages
		$msgs = FWS_Props::get()->msgs();
		if($msgs->contains_msg() || $doc->get_module()->error_occurred())
			$this->_handle_msgs($msgs);
		
		$doc->set_gzip($cfg['enable_gzip']);
	}

	/**
	 * Handles the collected messages
	 *
	 * @param FWS_Document_Messages $msgs
	 */
	private function _handle_msgs($msgs)
	{
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();
		
		$loginform = false;
		if($msgs->contains_no_access() || $this->_show_login)
		{
			if($user->is_loggedin())
				$msgs->add_error($locale->lang('permission_denied'));
			else
			{
				$functions->build_login_form();
				$loginform = true;
			}
		}
		
		if($msgs->contains_msg() || $loginform)
		{
			$amsgs = $msgs->get_all_messages();
			$links = $msgs->get_links();
			$tpl->set_template('inc_messages.htm');
			$tpl->add_variable_ref('errors',$amsgs[FWS_Document_Messages::ERROR]);
			$tpl->add_variable_ref('warnings',$amsgs[FWS_Document_Messages::WARNING]);
			$tpl->add_variable_ref('notices',$amsgs[FWS_Document_Messages::NOTICE]);
			$tpl->add_variable_ref('links',$links);
			$tpl->add_variables(array(
				'title' => $locale->lang('information'),
				'messages' => $msgs->contains_error() || $msgs->contains_notice() || $msgs->contains_warning(),
				'loginform' => $loginform
			));
			$tpl->restore_template();
		}
	}

	/**
	 * @see FWS_Document_Renderer_HTML_Default::content()
	 */
	protected function content()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$msgs = FWS_Props::get()->msgs();
		$functions = FWS_Props::get()->functions();
		$auth = FWS_Props::get()->auth();
		$doc = FWS_Props::get()->doc();

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
				if(!$module->is_always_viewable() && $cfg['enable_board'] == 0 &&
					!$user->is_admin())
				{
					$msg = nl2br(FWS_StringHelper::htmlspecialchars_back($cfg['board_disabled_text']));
					$msg .= $locale->lang('board_deactivated_notice');
					$msgs->add_notice($msg);
					$module->set_error();
				}
				// user banned?
				else if($functions->is_banned('ip',$user->get_user_ip()))
				{
					$msgs->add_notice($locale->lang('ip_banned'));
					$module->set_error();
				}
				else if(!$module_access)
				{
					$functions->build_login_form();
					$module->set_error();
					$this->_show_login = true;
				}
				else
					parent::content();
			}
			else
			{
				$functions->build_login_form();
				$module->set_error();
				$this->_show_login = true;
			}
		}
	}

	/**
	 * @see FWS_Document_Renderer_HTML_Default::header()
	 */
	protected function header()
	{
		$doc = FWS_Props::get()->doc();
		
		// add module-independend actions
		$actions = array(
			BS_ACTION_LOGOUT => 'logout',
			BS_ACTION_LOGIN => 'login',
			BS_ACTION_CHANGE_READ_STATUS => 'chg_read_status'
		);
		foreach($actions as $id => $action)
		{
			include_once(FWS_Path::server_app().'front/src/action/'.$action.'.php');
			$classname = 'BS_Front_Action_'.$action;
			$a = new $classname($id);
			$this->_action_perf->add_action($a);
		}
		
		// perform actions
		$this->perform_action();
		
		
		// prepare header-templates
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$unread = FWS_Props::get()->unread();

		$breadcrumbs = $this->get_breadcrumb_links();
		$page_title = str_replace($locale->lang('home'),$cfg['forum_title'],$breadcrumbs);
		$page_title = strip_tags($page_title);
		$this->set_title($page_title);

		$feedurl = BS_URL::get_mod_url('news_feed');
		
		// show page header
		$tpl->set_template('inc_header.htm');
		$tpl->add_variables(array(
			'cookie_path' => $cfg['cookie_path'],
			'cookie_domain' => $cfg['cookie_domain'],
			'theme' => $user->get_theme(),
			'title' => $this->get_title(),
			'forum_title' => $cfg['forum_title'],
			'charset' => 'charset='.$doc->get_charset(),
			'mimetype' => $doc->get_mimetype(),
			'cssfiles' => $this->get_css_files(),
			'cssblocks' => $this->get_css_blocks(),
			'jsfiles' => $this->get_js_files(),
			'jsblocks' => $this->get_js_blocks(),
			'action' => $input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING),
			'robots_value' => $this->get_robots_value(),
			'rss20_feed' => $feedurl->set(BS_URL_MODE,'rss20')->to_url(),
			'atom_feed' => $feedurl->set(BS_URL_MODE,'atom')->to_url(),
			'sig_max_height' => $cfg['sig_max_height'],
			'show_headline' => $this->_show_headline,
			'feeds_enabled' => $cfg['enable_news_feeds']
		));
		$tpl->restore_template();

		// show headline
		if($this->_show_headline)
		{
			$com = BS_Community_Manager::get_instance();
			
			$tpl->set_template('inc_headline.htm');
			$tpl->add_variables(array(
				'location' => $breadcrumbs,
				'breadcrumbs' => $this->get_breadcrumbs(),
				'action_type' => BS_ACTION_LOGIN,
				'login_url' => BS_URL::build_mod_url('login'),
				'show_deactivated_notice' => $cfg['enable_board'] == 0 && $user->is_admin(),
				'headline_url' => BS_URL::build_forums_url()
			));
			
			$top_links = array();
		
			// generate the buttons
			if($auth->has_acp_access())
			{
				$top_links[] = array(
					'title' => $locale->lang('adminarea'),
					'text' => $locale->lang('adminarea'),
					'url' => BS_URL::build_admin_url()
				);
			}
			if($this->_show_top_link('enable_memberlist','view_memberlist'))
			{
				$top_links[] = array(
					'title' => $locale->lang('memberlist_desc'),
					'text' => $locale->lang('memberlist'),
					'url' => BS_URL::build_mod_url('memberlist')
				);
			}
			if($this->_show_top_link('enable_linklist','view_linklist'))
			{
				$top_links[] = array(
					'title' => $locale->lang('linklist_desc'),
					'text' => $locale->lang('linklist'),
					'url' => BS_URL::build_mod_url('linklist')
				);
			}
			if($this->_show_top_link('enable_stats','view_stats'))
			{
				$top_links[] = array(
					'title' => $locale->lang('statistics_desc'),
					'text' => $locale->lang('statistics'),
					'url' => BS_URL::build_mod_url('stats')
				);
			}
			if($this->_show_top_link('enable_faq'))
			{
				$top_links[] = array(
					'title' => $locale->lang('faq_desc'),
					'text' => $locale->lang('faq'),
					'url' => BS_URL::build_mod_url('faq')
				);
			}
		
			if(!$user->is_loggedin())
			{
				if($cfg['enable_registrations'] && ($regurl = $com->get_register_url()))
				{
					$top_links[] = array(
						'title' => $locale->lang('register'),
						'text' => $locale->lang('register'),
						'url' => $regurl
					);
				}
			}
			else
			{
				$top_links[] = array(
					'title' => $locale->lang('yourprofile'),
					'text' => $locale->lang('profile'),
					'url' => BS_URL::build_sub_url('userprofile','infos')
				);
			}
		
			if($this->_show_top_link('enable_calendar','view_calendar'))
			{
				$top_links[] = array(
					'title' => $locale->lang('calendar_desc'),
					'text' => $locale->lang('calendar'),
					'url' => BS_URL::build_mod_url('calendar')
				);
			}
		
			if($this->_show_top_link('enable_search','view_search'))
			{
				$top_links[] = array(
					'title' => $locale->lang('search_desc'),
					'text' => $locale->lang('search'),
					'url' => BS_URL::build_mod_url('search')
				);
			}
		
			$tpl->add_variable_ref('top_links',$top_links);
		
			$username = '';
			$sendpw_url = '';
			$resend_url = '';
			$unread_news_title = '';
			if($user->is_loggedin())
			{
				$username = BS_UserUtils::get_link(
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
				$sendpw_url = $com->get_send_pw_url();
				$resend_url = $com->get_resend_act_url();
			}
			
			$lastlogin = false;
			if($user->is_loggedin())
			{
				if($ll = $input->get_var(BS_COOKIE_PREFIX.'lastlogin','cookie',FWS_Input::INTEGER))
					$lastlogin = FWS_Date::get_date($ll);
			}
			
			$cdate = new FWS_Date();
			$tpl->add_variables(array(
				'enable_portal' => $cfg['enable_portal'] == 1,
				'unread_news_title' => $unread_news_title,
				'enable_pms' => $user->is_loggedin() && $cfg['enable_pms'] == 1 &&
					$user->get_profile_val('allow_pms') == 1,
				'username' => $username,
				'username_plain' => $user->is_loggedin() ? $user->get_profile_val('user_name') : $username,
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
	 * @see FWS_Document_Renderer_HTML_Default::footer()
	 */
	protected function footer()
	{
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$db = FWS_Props::get()->db();
		$tpl = FWS_Props::get()->tpl();
		$functions = FWS_Props::get()->functions();
		$forums = FWS_Props::get()->forums();
		$unread = FWS_Props::get()->unread();
		$com = BS_Community_Manager::get_instance();
		
		if($this->_show_headline)
		{
			// set the first unread here to really get the next one. Because if we are currently
			// displaying an unread topic we will mark it as read in the module. So in the header
			// we have to old state and in the footer the new one
			$unread_topics = $unread->get_unread_topics();
			if(count($unread_topics) > 0)
			{
				list($utid,$udata) = each($unread_topics);
				$uurl = BS_URL::get_mod_url('redirect');
				$uurl->set(BS_URL_LOC,'show_post');
				$uurl->set(BS_URL_ID,$udata[0]);
				$first_unread_url = $uurl->to_url();
			}
			else
				$first_unread_url = '';
			
			$tpl->add_variables(array(
				'first_unread_url' => $first_unread_url,
				'unread_news_count' => $unread->get_unread_news_num(),
				'unread_topic_count' => $unread->get_length(),
				'unread_pm_count' => $user->is_loggedin() ? $user->get_profile_val('unread_pms') : 0,
			),'inc_headline.htm');
		}

		if($this->_show_bottom)
		{
			$options = array();
			if($cfg['enable_portal'] == 1)
				$options['portal'] = $locale->lang('portal');
			if($user->is_admin())
				$options['admin'] = $locale->lang('adminarea');
			if(!$user->is_loggedin() && $cfg['enable_registrations'] && $com->get_register_url())
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
				$doc = FWS_Props::get()->doc();
				$profiler = $doc->get_profiler();
				$qry_num = $db->get_query_count();
				$debug = $profiler->get_time().' '.$locale->lang('sec').', '
					.$qry_num.' '.$locale->lang('qrys').', '
					.FWS_StringHelper::get_formated_data_size($profiler->get_memory_usage());
			}
			
			$tpl->set_template('inc_bottom.htm');
			$tpl->add_variables(array(
				'insert_time' => $debug,
				'forums_init' => $functions->cache_basic_data(),
				'options' => $options,
				'register_url' => $com->get_register_url()
			));
		
			$tpl->add_variable_ref('forums',$nodes);
			$tpl->restore_template();
		}
				
		// show footer
		$tpl->set_template('inc_footer.htm');
		$tpl->add_variables(array(
			'queries' => BS_DEBUG == 2 ? FWS_Printer::to_string($db->get_queries()) : '',
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
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();

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
	
	protected function get_dump_vars()
	{
		return array_merge(parent::get_dump_vars(),get_object_vars($this));
	}
}
?>
