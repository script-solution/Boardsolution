<?php
/**
 * Contains the module-acpaccess-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module-acpaccess-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_acpaccess_module extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$auth = PLIB_Props::get()->auth();
		$locale = PLIB_Props::get()->locale();

		$module = $input->get_var('module','get',PLIB_Input::STRING);
		$groups = $input->get_var('groups','post');
		$user = $input->get_var('selectedUsers','post',PLIB_Input::STRING);

		// check if module exists
		if(BS_ACP_Module_ACPAccess_Helper::get_instance()->get_module_name($module) === '')
			return 'Unknown module "'.$module.'"';

		// at first we have to delete all groups and users for this module
		// because the user may have unselected groups / removed user
		BS_DAO::get_acpaccess()->delete_module($module);

		// add groups
		if(PLIB_Array_Utils::is_integer($groups))
		{
			$groups = array_unique($groups);
			foreach($groups as $gid)
			{
				// check if the usergroup exists
				if($cache->get_cache('user_groups')->key_exists($gid))
					BS_DAO::get_acpaccess()->create($module,'group',$gid);
			}
		}

		// now add the user
		if($uids = PLIB_StringHelper::get_ids($user))
		{
			$uids = array_unique($uids);
			foreach($uids as $uid)
			{
				// check if the user exists and if it is no admin
				$data = BS_DAO::get_profile()->get_user_by_id($uid);
				if($data === false)
					continue;
				
				if($auth->is_in_group($data['user_group'],BS_STATUS_ADMIN))
					continue;
				
				BS_DAO::get_acpaccess()->create($module,'user',$uid);
			}
		}

		// regenerate the cache from the database
		$cache->refresh('acp_access');
		
		$this->set_success_msg($locale->lang('saved_config_module_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>