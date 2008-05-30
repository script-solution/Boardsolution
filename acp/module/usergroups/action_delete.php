<?php
/**
 * Contains the delete-usergroups-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-usergroups-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_usergroups_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		// remove admins, guests and users
		$ids = array_diff($ids,array(BS_STATUS_GUEST,BS_STATUS_USER,BS_STATUS_ADMIN));
		
		// update the user-group
		foreach(BS_DAO::get_profile()->get_users_by_groups($ids) as $data)
		{
			$groups = PLIB_Array_Utils::advanced_explode(',',$data['user_group']);
			// remove all groups to remove from the groups of this user
			$new_groups = array_diff($groups,$ids);
			
			// if there is no other group, put the user in the BS_STATUS_USER-group
			if(count($new_groups) == 0)
				$new_groups[] = BS_STATUS_USER;
			
			BS_DAO::get_profile()->update_user_by_id(
				array('user_group' => implode(',',$new_groups).','),$data['id']
			);
		}
		
		BS_DAO::get_forums_perm()->delete_by_groups($ids);
	
		// we have to remove the entries in some tables which may contain the groups
		$id_str = implode(',',$ids);
		BS_DAO::get_acpaccess()->delete('group',$ids);
		BS_DAO::get_intern()->delete_by_groups($ids);
		
		// ok, now delete the groups themself
		$rows = BS_DAO::get_usergroups()->delete_by_ids($ids);
		
		// we have to refresh the cache
		$this->cache->refresh('intern');
		$this->cache->refresh('acp_access');
		
		if($rows > 0)
			$this->cache->refresh('user_groups');
		
		$this->set_success_msg($this->locale->lang('groups_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>