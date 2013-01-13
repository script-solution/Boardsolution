<?php
/**
 * Contains the client-acpaccess-action
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
 * The client-acpaccess-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_acpaccess_client extends BS_ACP_Action_Base
{
	function perform_action($type = 'user')
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$permissions = $input->get_var('permission','post');
		$aval = $input->get_var('aval','post',FWS_Input::ID);
		if($aval == null || $permissions == null)
			return 'POST-variable "aval" or "permission" is NULL';

		// check the access-value
		if($type == 'user')
		{
			$data = BS_DAO::get_profile()->get_user_by_id($aval);
			if($data === false)
				return 'The user with id "'.$aval.'" does not exist';
			
			if($auth->is_in_group($data['user_group'],BS_STATUS_ADMIN))
				return 'The selected user is an administrator!';
			
			$atype = 'user';
		}
		else
		{
			if(!$cache->get_cache('user_groups')->key_exists($aval) ||
					$aval == BS_STATUS_ADMIN || $aval == BS_STATUS_GUEST)
				return 'The selected group is admin, guest or doesn\'t exist';

			$atype = 'group';
		}

		// at first we have to delete all access for this client
		BS_DAO::get_acpaccess()->delete($atype,array($aval));

		// now enable the specified values
		foreach($permissions as $module => $val)
		{
			if(BS_ACP_Module_ACPAccess_Helper::get_module_name($module) === '')
				continue;

			if($val == 1)
				BS_DAO::get_acpaccess()->create($module,$atype,$aval);
		}

		// regenerate the cache from the database
		$cache->refresh('acp_access');
		
		$this->set_success_msg($locale->lang('saved_config_client_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>