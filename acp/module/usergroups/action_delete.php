<?php
/**
 * Contains the delete-usergroups-action
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$id_str = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		// remove admins, guests and users
		$ids = array_diff($ids,array(BS_STATUS_GUEST,BS_STATUS_USER,BS_STATUS_ADMIN));
		
		// update the user-group
		foreach(BS_DAO::get_profile()->get_users_by_groups($ids) as $data)
		{
			$groups = FWS_Array_Utils::advanced_explode(',',$data['user_group']);
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
		$cache->refresh('intern');
		$cache->refresh('acp_access');
		
		if($rows > 0)
			$cache->refresh('user_groups');
		
		$this->set_success_msg($locale->lang('groups_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>