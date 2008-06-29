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
final class BS_Front_Page extends BS_Document
{
	/**
	 * The current module
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
	 * Constructor
	 */
	public function __construct()
	{
		try
		{
			$times = array();
			
			if(BS_DEBUG > 1)
				$times['init'] = new PLIB_Timer();
			
			parent::__construct();
			
			$this->_module = $this->_load_module();
			
			if(BS_DEBUG > 1)
				$times['init'] = $times['init']->stop();
			
			$this->_start_document($this->cfg['enable_gzip']);
			
			// output
			if(BS_DEBUG > 1)
				$times['head'] = new PLIB_Timer();
			
			$this->_add_head();
			
			if(BS_DEBUG > 1)
				$times['head'] = $times['head']->stop();
			
			if(BS_DEBUG > 1)
				$times['module'] = new PLIB_Timer();
			
			$this->_add_module();
			
			if(BS_DEBUG > 1)
				$times['module'] = $times['module']->stop();
			
			if(BS_DEBUG > 1)
				$times['foot'] = new PLIB_Timer();
				
			$this->_add_foot();
			
			if(BS_DEBUG > 1)
				$times['foot'] = $times['foot']->stop();
			
			if(BS_DEBUG > 1)
				$times['tpl'] = new PLIB_Timer();
			
			// add redirect information
			$redirect = $this->get_redirect();
			if($redirect)
				$this->tpl->add_array('redirect',$redirect,'inc_header.htm');
			
			// notify the template if an error has occurred
			$this->tpl->add_global('module_error',$this->_module->error_occurred());
			
			// add messages
			$this->msgs->print_messages();
			
			if($this->_template != '')
				echo $this->tpl->parse_template($this->_template);
			else
				echo $this->tpl->parse_template($this->_module->get_template());
			
			if(BS_DEBUG > 1)
				$times['tpl'] = $times['tpl']->stop();
	
			if(BS_DEBUG > 1)
				echo PLIB_PrintUtils::to_string($times);
			
			$this->_finish();
	
			$this->_send_document($this->cfg['enable_gzip']);
		}
		catch(PLIB_Exceptions_Critical $e)
		{
			echo $e;
		}
	}

	/**
	 * Loads the corresponding module
	 *
	 */
	private function _load_module()
	{
		// determine start-module
		if($this->cfg['enable_portal'] == 1 &&
			($this->user->is_loggedin() || $this->user->get_profile_val('startmodule' == 'portal')))
			$default = 'portal';
		else
			$default = 'forums';
		
		$this->_module_name = PLIB_Helper::get_module_name(
			'BS_Front_Module_',BS_URL_ACTION,$default,'front/module/'
		);
		$class = 'BS_Front_Module_'.$this->_module_name;
		$c = new $class();

		$this->_action_perf->set_prefix('BS_Front_Action_');

		// add module-independend actions
		$actions = array(
			BS_ACTION_LOGOUT => 'logout',
			BS_ACTION_LOGIN => 'login',
			BS_ACTION_CHANGE_READ_STATUS => 'chg_read_status'
		);
		foreach($actions as $id => $action)
		{
			include_once(PLIB_Path::inner().'front/src/action/'.$action.'.php');
			$classname = 'BS_Front_Action_'.$action;
			$a = new $classname($id);
			$this->_action_perf->add_action($a);
		}

		// add actions of the current module
		$this->_action_perf->add_actions($this->_module_name,$c->get_actions());

		return $c;
	}

	/**
	 * Adds the loaded module to the template
	 *
	 */
	private function _add_module()
	{
		// check this here (before the action will be performed)
		$board_access = $this->_module->auth->has_board_access() || $this->_module->is_guest_only();
		$module_access = $this->_module->has_access();
		
		// perform actions
		$this->perform_actions();
		
		$action_result = $this->get_action_result();
		
		// Note that we may do this here because the template will be parsed later
		// after all is finished!
		
		// add global variables
		$this->tpl->add_global('action_result',$action_result);
		$this->tpl->add_global('module_error',false);
		
		if($action_result < 1)
		{
			// the modules register, sendpw, change_password and resend_activation are always allowed for
			// guests
			if($board_access)
			{
				// board deactivated?
				if($this->_module_name != 'login' && $this->cfg['enable_board'] == 0 &&
					!$this->user->is_admin())
				{
					$msg = nl2br(PLIB_StringHelper::htmlspecialchars_back($this->cfg['board_disabled_text']));
					$msg .= $this->locale->lang('board_deactivated_notice');
					$this->msgs->add_notice($msg);
					$this->_module->set_error();
				}
				// user banned?
				else if($this->functions->is_banned('ip',$this->user->get_user_ip()))
				{
					$this->msgs->add_notice($this->locale->lang('ip_banned'));
					$this->_module->set_error();
				}
				else if(!$module_access)
				{
					$this->functions->show_login_form();
					$this->_module->set_error();
				}
				else
				{
					$this->tpl->set_template($this->_module->get_template());
					$this->_module->run();
					$this->tpl->restore_template();
				}
			}
		}
	}

	/**
	 * Addss the header to the template
	 *
	 */
	private function _add_head()
	{
		$title = PLIB_Helper::generate_location(
			$this->_module,$this->locale->lang('home'),$this->url->get_forums_url()
		);

		// show page header
		$this->tpl->set_template('inc_header.htm');
		$this->tpl->add_variables(array(
			'root' => PLIB_Path::inner(),
			'cookie_path' => $this->cfg['cookie_path'],
			'cookie_domain' => $this->cfg['cookie_domain'],
			'theme' => $this->user->get_theme(),
			'forum_title' => $this->cfg['forum_title'],
			'page_title' => $title['title'],
			'action' => $this->input->get_var(BS_URL_ACTION,'get',PLIB_Input::STRING),
			'robots_value' => $this->_module->get_robots_value(),
			'charset' => 'charset='.BS_HTML_CHARSET,
			'rss20_feed' => $this->url->get_standalone_url('front','news_feed','&amp;'.BS_URL_MODE.'=rss20'),
			'atom_feed' => $this->url->get_standalone_url('front','news_feed','&amp;'.BS_URL_MODE.'=atom'),
			'sig_max_height' => $this->cfg['sig_max_height']
		));
		$this->tpl->restore_template();

		// show headline
		$this->tpl->set_template('inc_headline.htm');
		$this->tpl->add_variables(array(
			'location' => $title['position'],
			'action_type' => BS_ACTION_LOGIN,
			'login_url' => $this->url->get_url('login'),
			'show_deactivated_notice' => $this->cfg['enable_board'] == 0 && $this->user->is_admin(),
			'headline_url' => $this->url->get_forums_url()
		));
		
		$top_links = array();
	
		// generate the buttons
		if($this->auth->has_acp_access())
		{
			$top_links[] = array(
				'title' => $this->locale->lang('adminarea'),
				'text' => $this->locale->lang('adminarea'),
				'url' => $this->url->get_admin_url()
			);
		}
		if($this->_show_top_link('enable_memberlist','view_memberlist'))
		{
			$top_links[] = array(
				'title' => $this->locale->lang('memberlist_desc'),
				'text' => $this->locale->lang('memberlist'),
				'url' => $this->url->get_url('memberlist')
			);
		}
		if($this->_show_top_link('enable_linklist','view_linklist'))
		{
			$top_links[] = array(
				'title' => $this->locale->lang('linklist_desc'),
				'text' => $this->locale->lang('linklist'),
				'url' => $this->url->get_url('linklist')
			);
		}
		if($this->_show_top_link('enable_stats','view_stats'))
		{
			$top_links[] = array(
				'title' => $this->locale->lang('statistics_desc'),
				'text' => $this->locale->lang('statistics'),
				'url' => $this->url->get_url('stats')
			);
		}
		if($this->_show_top_link('enable_faq'))
		{
			$top_links[] = array(
				'title' => $this->locale->lang('faq_desc'),
				'text' => $this->locale->lang('faq'),
				'url' => $this->url->get_url('faq')
			);
		}
	
		if(!$this->user->is_loggedin())
		{
			if($this->cfg['enable_registrations'] && (!BS_ENABLE_EXPORT || BS_EXPORT_REGISTER_TYPE == 'link'))
			{
				$top_links[] = array(
					'title' => $this->locale->lang('register'),
					'text' => $this->locale->lang('register'),
					'url' => !BS_ENABLE_EXPORT ? $this->url->get_url('register') : BS_EXPORT_REGISTER_LINK
				);
			}
		}
		else
		{
			$top_links[] = array(
				'title' => $this->locale->lang('yourprofile'),
				'text' => $this->locale->lang('profile'),
				'url' => $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=pr_infos')
			);
		}
	
		if($this->_show_top_link('enable_calendar','view_calendar'))
		{
			$top_links[] = array(
				'title' => $this->locale->lang('calendar_desc'),
				'text' => $this->locale->lang('calendar'),
				'url' => $this->url->get_url('calendar')
			);
		}
	
		if($this->_show_top_link('enable_search','view_search'))
		{
			$top_links[] = array(
				'title' => $this->locale->lang('search_desc'),
				'text' => $this->locale->lang('search'),
				'url' => $this->url->get_url('search')
			);
		}
	
		$this->tpl->add_array('top_links',$top_links);
	
		$username = '';
		$sendpw_url = '';
		$reg_url = '';
		$resend_url = '';
		$unread_news_title = '';
		if($this->user->is_loggedin())
		{
			$username = BS_UserUtils::get_instance()->get_link(
				$this->user->get_user_id(),$this->user->get_profile_val('user_name'),
				$this->user->get_profile_val('user_group')
	  	);
	  	
			$news_num = $this->unread->get_unread_news_num();
			$unread_news_title = $this->locale->lang('portal').' ( ';
			$unread_news_title .= sprintf($this->locale->lang('unread_news'),$news_num).' )';
		}
		else
		{
			$username = $this->locale->lang('guest');
			if(!BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE != 'disabled')
			{
				if(!BS_ENABLE_EXPORT || BS_EXPORT_SEND_PW_TYPE == 'enabled')
					$sendpw_url = $this->url->get_url('sendpw');
				else
					$sendpw_url = BS_EXPORT_SEND_PW_LINK;
			}
	
			if($this->cfg['enable_registrations'] && (!BS_ENABLE_EXPORT || BS_EXPORT_REGISTER_TYPE == 'link'))
			{
				if(!BS_ENABLE_EXPORT)
					$reg_url = $this->url->get_url('register');
				else
					$reg_url = BS_EXPORT_REGISTER_LINK;
			}
			
			if(!BS_ENABLE_EXPORT || BS_EXPORT_RESEND_ACT_TYPE == 'link')
			{
				if(!BS_ENABLE_EXPORT)
					$resend_url = $this->url->get_url('resend_activation');
				else
					$resend_url = BS_EXPORT_RESEND_ACT_LINK;
			}
		}
		
		$lastlogin = false;
		if($this->user->is_loggedin())
		{
			if($ll = $this->input->get_var(BS_COOKIE_PREFIX.'lastlogin','cookie',PLIB_Input::INTEGER))
				$lastlogin = PLIB_Date::get_date($ll);
		}
		
		$cdate = new PLIB_Date();
		$this->tpl->add_variables(array(
			'location' => $title['position'],
			'enable_portal' => $this->cfg['enable_portal'] == 1,
			'unread_news_title' => $unread_news_title,
			'enable_pms' => $this->user->is_loggedin() && $this->cfg['enable_pms'] == 1 &&
				$this->user->get_profile_val('allow_pms') == 1,
			'unread_topic_count' => $this->unread->get_length(),
			'unread_pm_count' => $this->user->is_loggedin() ? $this->user->get_profile_val('unread_pms') : 0,
			'unread_news_count' => $this->unread->get_unread_news_num(),
			'username' => $username,
			'register_link' => $reg_url,
			'forgotpw_link' => $sendpw_url,
			'resendact_link' => $resend_url,
			'current_date' => $cdate->to_format('longdate',true),
			'current_time' => $cdate->to_format('time_s'),
			'lastlogin' => $lastlogin
		));
		$this->tpl->restore_template();
	}

	/**
	 * Adds the footer to the template
	 *
	 */
	private function _add_foot()
	{
		$options = array();
		if($this->cfg['enable_portal'] == 1)
			$options['portal'] = $this->locale->lang('portal');
		if($this->user->is_admin())
			$options['admin'] = $this->locale->lang('adminarea');
		if(!$this->user->is_loggedin() && $this->cfg['enable_registrations'] &&
				(!BS_ENABLE_EXPORT || BS_EXPORT_REGISTER_TYPE == 'link'))
			$options['register'] = $this->locale->lang('register');
		else if($this->user->is_loggedin())
			$options['profile'] = $this->locale->lang('profile');
		if($this->cfg['enable_memberlist'] == 1)
			$options['memberlist'] = $this->locale->lang('memberlist');
		if($this->cfg['enable_stats'] == 1)
			$options['stats'] = $this->locale->lang('statistics');
		if($this->cfg['enable_calendar'] == 1)
			$options['calendar'] = $this->locale->lang('calendar');
		if($this->cfg['enable_search'] == 1)
			$options['search'] = $this->locale->lang('search');
		if($this->cfg['enable_linklist'] == 1)
			$options['linklist'] = $this->locale->lang('linklist');
		if($this->cfg['enable_faq'] == 1)
			$options['faq'] = $this->locale->lang('faq');
		if($this->user->is_loggedin() && $this->cfg['enable_pms'] == 1)
			$options['pms'] = $this->locale->lang('privatemessages');
		if($this->user->is_loggedin())
			$options['unread'] = $this->locale->lang('unread_threads');
		$options['team'] = $this->locale->lang('the_team');
		if($this->auth->has_global_permission('view_online_locations'))
			$options['userloc'] = $this->locale->lang('user_locations');
		
		$debug = '&nbsp;';
		if(BS_DEBUG > 0)
		{
			$qry_num = $this->db->get_performed_query_num();
			$debug = $this->doc->get_script_time().' '.$this->locale->lang('sec').', '
				.$qry_num.' '.$this->locale->lang('qrys').', '
				.PLIB_StringHelper::get_formated_data_size(memory_get_peak_usage());
		}
		
		$this->tpl->set_template('inc_bottom.htm');
		$this->tpl->add_variables(array(
			'insert_time' => $debug,
			'forums_init' => $this->functions->cache_basic_data(),
			'options' => $options
		));
	
		$forums = array();
		$forum_data = $this->forums->get_all_nodes();
		$forum_num = count($forum_data);
		for($i = 0;$i < $forum_num;$i++)
		{
			$fnode = $forum_data[$i];
			$fdata = $fnode->get_data();
			
			// display forum?
			if($this->cfg['hide_denied_forums'] == 1 &&
				 !$this->auth->has_access_to_intern_forum($fdata->get_id()))
				continue;
	
			$forum_name = '';
			for($x = 0;$x < $fnode->get_layer();$x++)
				$forum_name .= ' --';
			$forum_name .= ' '.$fdata->get_name();
	
			$forums[] = array(
				'forum_id' => $fdata->get_id(),
				'forum_name' => $forum_name
			);
		}
		
		$this->tpl->add_array('forums',$forums);
		$this->tpl->restore_template();
		
		// show footer
		$this->tpl->set_template('inc_footer.htm');
		$this->tpl->add_variables(array(
			'queries' => BS_DEBUG == 2 ? PLIB_PrintUtils::to_string($this->db->get_performed_queries()) : ''
		));
		$this->tpl->restore_template();
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
		$display = true;
		if($this->cfg[$cfg_entry] == 0)
			$display = false;
	
		if($auth_entry != '' && !$this->cfg['display_denied_options'] &&
			 !$this->auth->has_global_permission($auth_entry))
		{
			$display = false;
		}
	
		return $display;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>