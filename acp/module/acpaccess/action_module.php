<?php
/**
 * Contains the module-acpaccess-action
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
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();

		$module = $input->get_var('module','get',FWS_Input::STRING);
		$groups = $input->get_var('groups','post');
		$user = $input->get_var('selectedUsers','post',FWS_Input::STRING);

		// check if module exists
		if(BS_ACP_Module_ACPAccess_Helper::get_module_name($module) === '')
			return 'Unknown module "'.$module.'"';

		// at first we have to delete all groups and users for this module
		// because the user may have unselected groups / removed user
		BS_DAO::get_acpaccess()->delete_module($module);

		// add groups
		if(FWS_Array_Utils::is_integer($groups))
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
		if($uids = FWS_StringHelper::get_ids($user))
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