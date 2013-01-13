<?php
/**
 * Contains the delete-useractivation-action
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
 * The delete-useractivation-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_useractivation_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$com = BS_Community_Manager::get_instance();

		if(!$com->is_user_management_enabled())
			return 'The user-management is disabled';
		
		$idstr = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';
		
		// delete user
		BS_DAO::get_user()->delete($ids);
		BS_DAO::get_profile()->delete($ids);
		
		// delete from activation-table
		BS_DAO::get_activation()->delete_by_users($ids);
		
		// send email
		BS_ACP_Utils::send_email_to_user(
			BS_EmailFactory::get_instance()->get_account_not_activated_mail(),
			$ids
		);
		
		$this->set_success_msg($locale->lang('accounts_deleted_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>