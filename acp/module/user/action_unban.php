<?php
/**
 * Contains the unban-user-action
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
 * The unban-user-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_user_unban extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		$idstr = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';

		// reactivate the user
		BS_DAO::get_profile()->update_users_by_ids(array('banned' => 0),$ids);

		// send the emails and collect errors
		$email = BS_EmailFactory::get_instance()->get_account_reactivated_mail();
		$error_msgs = array();
		foreach(BS_DAO::get_profile()->get_users_by_ids($ids) as $data)
		{
			// fire community-event
			$user = BS_Community_User::get_instance_from_data($data);
			BS_Community_Manager::get_instance()->fire_user_reactivated($user);
			
			$email->set_recipient($data['user_email']);
			if(!$email->send_mail())
			{
				$error = $email->get_error_message();
				if(!isset($error_msgs[$error]))
					$error_msgs[$error] = true;
			}
		}
		
		// add errors
		foreach(array_keys($error_msgs) as $msg)
		{
			if($msg)
				$msgs->add_error($msg);
		}
	
		$this->set_success_msg($locale->lang('user_reactivated_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>