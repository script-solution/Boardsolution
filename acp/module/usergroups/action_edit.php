<?php
/**
 * Contains the edit-usergroups-action
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
 * The edit-usergroups-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_usergroups_edit extends BS_ACP_Action_Base
{
	public function perform_action($type = 'edit')
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		if($type == 'edit')
		{
			$id = $input->get_var('id','get',FWS_Input::ID);
			if($id == null)
				return 'The id "'.$id.'" is invalid';
		}

		$group_title = $input->get_var('group_title','post',FWS_Input::STRING);
		if(trim($group_title) == '')
			return 'group_title_missing';

		// collect values to update/insert
		$values = array();
		$values['group_title'] = $group_title;
		$values['is_visible'] = 1;
		
		if($type == 'add' || $id != BS_STATUS_GUEST)
		{
			$group_color = $input->get_var('group_color','post',FWS_Input::STRING);
			if(!preg_match('/^[a-f0-9]{6}$/i',$group_color))
				return 'invalid_group_color';

			$overrides_mod = $input->get_var('overrides_mod','post',FWS_Input::INT_BOOL);
			$gr_filled_image = $input->get_var('group_rank_filled_image','post',FWS_Input::STRING);
			$gr_empty_image = $input->get_var('group_rank_empty_image','post',FWS_Input::STRING);
			$is_visible = $input->get_var('is_visible','post',FWS_Input::INT_BOOL);
			$is_team = $input->get_var('is_team','post',FWS_Input::INT_BOOL);

			$values['group_color'] = $group_color;
			$values['group_rank_filled_image'] = $gr_filled_image;
			$values['group_rank_empty_image'] = $gr_empty_image;
			$values['overrides_mod'] = $overrides_mod;
			if($type == 'edit' && ($id == BS_STATUS_USER || $id == BS_STATUS_ADMIN))
				$values['is_visible'] = 1;
			else
				$values['is_visible'] = $is_visible;
			$values['is_team'] = $is_team;
		}

		$guest_disallowed = BS_ACP_Module_UserGroups_Helper::get_guest_disallowed();
		foreach(BS_ACP_Module_UserGroups_Helper::get_permissions() as $name)
		{
			if($type == 'add' || $id != BS_STATUS_GUEST || !in_array($name,$guest_disallowed))
				$values[$name] = $input->get_var($name,'post',FWS_Input::INT_BOOL);
		}
		
		// update db
		if($type == 'add')
			BS_DAO::get_usergroups()->create($values);
		else
		{
			// made invisible?
			$data = BS_DAO::get_usergroups()->get_by_id($id);
			if($data['is_visible'] == 1 && $values['is_visible'] == 0)
			{
				// ok, we assign all users, that have this group as main-group the group BS_STATUS_USER as
				// main-group
				foreach(BS_DAO::get_profile()->get_users_by_maingroup($id) as $row)
				{
					$groups = FWS_Array_Utils::advanced_explode(',',$row['user_group']);
					$groups[0] = BS_STATUS_USER;
					BS_DAO::get_profile()->update_user_by_id(
						array('user_group' => implode(',',$groups).','),$row['id']
					);
				}
			}
			
			BS_DAO::get_usergroups()->update_by_id($id,$values);
		}
		
		// refresh cache
		$cache->refresh('user_groups');
		
		// finish
		if($type == 'add')
			$this->set_success_msg($locale->lang('group_add_success'));
		else
			$this->set_success_msg($locale->lang('group_edit_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>