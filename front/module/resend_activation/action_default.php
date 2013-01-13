<?php
/**
 * Contains the resend_activation-action
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The resend_activation-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_resend_activation_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$com = BS_Community_Manager::get_instance();

		if($user->is_loggedin())
			return 'You are loggedin';

		if(!$com->is_resend_act_enabled())
			return 'Resend-act-link is disabled';

		if(!$functions->check_security_code())
			return 'invalid_security_code';

		$email = $input->get_var('email','post',FWS_Input::STRING);
		if($email === null)
			return 'The email-address "'.$email.'" is invalid';

		$userdata = BS_DAO::get_profile()->get_user_by_email($email);
		if($userdata === false)
			return 'sendpw_invalid_email';

		if($userdata['active'] == 1)
			return 'sendpw_user_activated';
		
		$act = BS_DAO::get_activation()->get_by_user($userdata['id']);
		if($act === false)
			return 'No activation-entry found';
		
		$mail = BS_EmailFactory::get_instance()->get_account_activation_mail(
			$userdata['id'],$email,$act['user_key']
		);
		if(!$mail->send_mail())
			return sprintf($locale->lang('error_mail_error'),$mail->get_error_message());

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back'),BS_URL::get_start_url());

		return '';
	}
}
?>