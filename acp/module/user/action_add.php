<?php
/**
 * Contains the add-user-action
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
 * The add-user-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_user_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$cache = FWS_Props::get()->cache();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();
		$com = BS_Community_Manager::get_instance();

		if(!$com->is_user_management_enabled())
			return 'The user-management is disabled';
		
		// check username
		$user_name = $input->get_var('user_name','post',FWS_Input::STRING);
		if(trim($user_name) == '' || !BS_UserUtils::check_username($user_name))
			return 'usernamenotallowed';

		if($functions->is_banned('user',$user_name))
			return 'user_name_banned';

		if(BS_DAO::get_user()->name_exists($user_name))
			return 'registeruservorhanden';

		$user_pw = $input->get_var('user_pw','post',FWS_Input::STRING);
		$user_pw_conf = $input->get_var('user_pw_conf','post',FWS_Input::STRING);
		$user_email = $input->get_var('user_email','post',FWS_Input::STRING);
		$main_group = $input->get_var('main_group','post',FWS_Input::ID);
		$other_groups = $input->get_var('other_groups','post');
		$notify = $input->get_var('notify','post',FWS_Input::INT_BOOL);

		// check pw
		if($user_pw == '' || $user_pw != $user_pw_conf)
			return 'registerpwsnichtidentisch';

		// check email
		$user_email = trim($user_email);
		if(!FWS_StringHelper::is_valid_email($user_email))
			return 'mailnotallowed';

		if($functions->is_banned('mail',$user_email))
			return 'email_is_banned';

		// does the email already exist?
		if(BS_DAO::get_user()->email_exists($user_email))
			return 'email_exists';

		// create user
		$id = BS_DAO::get_user()->create($user_name,$user_email,$user_pw);

		// build user-groups
		$groups = array();
		if($cache->get_cache('user_groups')->key_exists($main_group))
			$groups[] = $main_group;

		if(is_array($other_groups))
		{
			foreach($other_groups as $gid)
			{
				if($cache->get_cache('user_groups')->key_exists($gid))
					$groups[] = $gid;
			}
		}
		$groups = array_unique($groups);
		
		// create profile-entry
		BS_DAO::get_profile()->create($id,$groups);
		
		// fire community-event
		$status = BS_Community_User::get_status_from_groups($groups);
		$user = new BS_Community_User(
			$id,$input->unescape_value($user_name,'post'),
			$input->unescape_value($user_email,'post'),$status,md5($user_pw),
			$input->unescape_value($user_pw,'post')
		);
		BS_Community_Manager::get_instance()->fire_user_registered($user);

		// send email if required
		if($notify == 1)
		{
			$email = BS_EmailFactory::get_instance()->get_new_registration_mail(
				$user_name,$user_email,$user_pw
			);
			if(!$email->send_mail())
			{
				$msg = $email->get_error_message();
				$msgs->add_error(sprintf($locale->lang('email_send_error'),$msg));
			}
		}
		
		$this->set_success_msg($locale->lang('add_user_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>