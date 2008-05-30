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
	
	public function get_location()
	{
		switch($this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING))
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
		
		$loc = array(
			$this->locale->lang($title) => ''
		);
		return array_merge($loc,$this->_sub->get_location());
	}
	
	public function get_template()
	{
		return 'userprofile.htm';
	}
	
	public function run()
	{
		// run submodule
		parent::run();
		
		// display navigation
		$inbox_num = 0;
		$outbox_num = 0;
		$inbox_status = '';
		$outbox_status = '';
	
		if($this->cfg['enable_pms'] == 1 && $this->user->get_profile_val('allow_pms') == 1)
		{
			$helper = BS_Front_Module_UserProfile_Helper::get_instance();
			$inbox_num = $helper->get_inbox_num();
			$inbox_status = $this->_show_profile_status_bar($inbox_num,$this->cfg['pm_max_inbox']);
			$outbox_num = $helper->get_outbox_num();
			$outbox_status = $this->_show_profile_status_bar($outbox_num,$this->cfg['pm_max_outbox']);
		}
	
		$this->tpl->add_variables(array(
			'max_inbox' => $this->cfg['pm_max_inbox'],
			'max_outbox' => $this->cfg['pm_max_outbox'],
			'allow_pw_change' => !BS_ENABLE_EXPORT,
			'enable_avatars' => $this->cfg['enable_avatars'],
			'enable_signatures' => $this->cfg['enable_signatures'],
			'enable_email_notification' => $this->cfg['enable_email_notification'] == 1,
			'subscribe_forums_perm' => $this->cfg['enable_email_notification'] == 1 &&
				$this->auth->has_global_permission('subscribe_forums'),
			'enable_pms' => $this->cfg['enable_pms'] == 1 && $this->user->get_profile_val('allow_pms') == 1,
			'user_pw_change_title' => $this->cfg['profile_max_user_changes'] != 0 ?
				$this->locale->lang('user_n_pw_change') : $this->locale->lang('pw_change'),
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
		$percent = $max == 0 ? 0 : floor((130 / $max) * $num);
		$img = $this->user->get_theme_item_path('images/diagrams/profile_diagram.gif');
		return '<img src="'.$img.'" height="16" width="'.$percent.'" alt="'.$percent.'" />';
	}

	public function has_access()
	{
		return $this->user->is_loggedin();
	}
}
?>