<?php
/**
 * Contains the userprofile-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The userprofile-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_userprofile extends BS_Front_SubModuleContainer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$subs = array(
			// profile
			'infos','config','signature','avatars','chpw','favforums',
			// subscriptions
			'forums','topics',
			// pms
			'pmcompose','pmdetails','pmbanlist','pminbox','pmoutbox','pmoverview','pmsearch'
		);
		parent::__construct('userprofile',$subs,'infos');
	}
	
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($user->is_loggedin());
		
		$renderer->set_template('userprofile.htm');

		switch($input->get_var(BS_URL_SUB,'get',FWS_Input::STRING))
		{
			case 'forums':
			case 'topics':
				$title = 'subscriptions';
				break;
			case 'pmoverview':
			case 'pminbox':
			case 'pmoutbox':
			case 'pmcompose':
			case 'pmbanlist':
			case 'pmdetails':
			case 'pmsearch':
				$title = 'privatemessages';
				break;
			default:
				$title = 'profile';
				break;
		}
		
		$renderer->add_breadcrumb($locale->lang($title),'');
		
		// init submodule
		$this->_sub->init($doc);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();

		// run submodule
		parent::run();
		
		// display navigation
		$inbox_num = 0;
		$outbox_num = 0;
		$inbox_status = '';
		$outbox_status = '';
	
		if($cfg['enable_pms'] == 1 && $user->get_profile_val('allow_pms') == 1)
		{
			$helper = BS_Front_Module_UserProfile_Helper::get_instance();
			$inbox_num = $helper->get_inbox_num();
			$inbox_status = $this->_show_profile_status_bar($inbox_num,$cfg['pm_max_inbox']);
			$outbox_num = $helper->get_outbox_num();
			$outbox_status = $this->_show_profile_status_bar($outbox_num,$cfg['pm_max_outbox']);
		}
	
		$tpl->add_variables(array(
			'max_inbox' => $cfg['pm_max_inbox'],
			'max_outbox' => $cfg['pm_max_outbox'],
			'allow_pw_change' => !BS_ENABLE_EXPORT,
			'enable_avatars' => $cfg['enable_avatars'],
			'enable_signatures' => $cfg['enable_signatures'],
			'enable_email_notification' => $cfg['enable_email_notification'] == 1,
			'subscribe_forums_perm' => $cfg['enable_email_notification'] == 1 &&
				$auth->has_global_permission('subscribe_forums'),
			'enable_pms' => $cfg['enable_pms'] == 1 && $user->get_profile_val('allow_pms') == 1,
			'user_pw_change_title' => $cfg['profile_max_user_changes'] != 0 ?
				$locale->lang('user_n_pw_change') : $locale->lang('pw_change'),
			'inbox_num' => $inbox_num,
			'outbox_num' => $outbox_num,
			'inbox_status' => $inbox_status,
			'outbox_status' => $outbox_status,
			'content_tpl' => $this->_sub->get_template()
		));
	}
	
	/**
	 * displays a statusbar to show the full-status of the PM-folders
	 * 
	 * @param int $num the current number of items
	 * @param int $max the maximum allowed number
	 * @return string the HTML-code for the image
	 */
	private function _show_profile_status_bar($num,$max)
	{
		$user = FWS_Props::get()->user();

		$percent = $max == 0 ? 0 : floor((100 / $max) * $num);
		$img = $user->get_theme_item_path('images/diagrams/profile_diagram.gif');
		return '<img src="'.$img.'" height="16" width="'.$percent.'%" alt="'.$percent.'" />';
	}
}
?>