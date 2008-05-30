<?php
/**
 * Contains the pmbanlist-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pmbanlist submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pmbanlist extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_BAN_USER => 'pmbanuser',
			BS_ACTION_UNBAN_USER => 'pmunbanuser'
		);
	}
	
	public function run()
	{
		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}

		$del = $this->input->get_var('del','post');
		if($del != null && PLIB_Array_Utils::is_integer($del))
		{
			$ids = implode(',',$del);
			$names = array();
			foreach(BS_DAO::get_userbans()->get_by_user($this->user->get_user_id(),$del) as $i => $data)
				$names[] = $data['user_name'];
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));

			$loc = '&amp;'.BS_URL_LOC.'=pmbanlist';
			$yes_url = $this->url->get_url(0,$loc.'&amp;'.BS_URL_AT.'='
				.BS_ACTION_UNBAN_USER.'&amp;'.BS_URL_DEL.'='.$ids,'&amp;',true);
			$no_url = $this->url->get_url(0,$loc);
			$target_url = $this->url->get_url('redirect','&amp;'.BS_URL_LOC.'=del_pm_ban'
				.'&amp;'.BS_URL_ID.'='.$ids);
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('banlist_delete'),$namelist),$yes_url,$no_url,$target_url
			);
		}

		$this->tpl->add_variables(array(
			'action_param' => BS_URL_ACTION
		));
		
		$banned_user = array();
		foreach(BS_DAO::get_userbans()->get_all_of_user($this->user->get_user_id()) as $i => $data)
		{
			$banned_user[] = array(
				'number' => $i + 1,
				'user_name' => BS_UserUtils::get_instance()->get_link($data['baned_user'],$data['user_name'],
					$data['user_group']),
				'id' => $data['id']
			);
		}
		
		$this->tpl->add_array('banned_user',$banned_user);
	
		$this->tpl->add_variables(array(
			'ban_user_url' => $this->url->get_url(
				'userprofile',
				'&amp;'.BS_URL_LOC.'=pmbanlist'.'&amp;'.BS_URL_AT.'='.BS_ACTION_BAN_USER,
				'&amp;',
				true
			)
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('banlist') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=pmbanlist')
		);
	}
}
?>