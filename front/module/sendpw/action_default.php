<?php
/**
 * Contains the sendpw-action
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
 * The sendpw-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_sendpw_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$com = BS_Community_Manager::get_instance();

		// check if the user is allowed to do this
		if(!$com->is_send_pw_enabled() || $user->is_loggedin())
			return 'Send-pw is disabled or you are a guest';

		if(!$functions->check_security_code())
			return 'invalid_security_code';

		// check if the email exists
		$email_address = $input->get_var('email','post',FWS_Input::STRING);
		$data = BS_DAO::get_user()->get_user_by_email($email_address);
		if($data === false)
			return 'sendpw_invalid_email';
		
		// send the email
		$key = FWS_StringHelper::generate_random_key();
		$email = BS_EmailFactory::get_instance()->get_change_pw_mail($data['id'],$email_address,$key);
		if(!$email->send_mail())
			return sprintf($locale->lang('error_mail_error'),$email->get_error_message());

		// create the entry / update the entry
		if(BS_DAO::get_changepw()->exists($data['id']))
			BS_DAO::get_changepw()->update_by_user($data['id'],$key);
		else
			BS_DAO::get_changepw()->create($data['id'],$key);

		$this->set_action_performed(true);
		$this->add_link($locale->lang('forumindex'),BS_URL::get_start_url());

		return '';
	}
}
?>