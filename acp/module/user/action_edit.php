<?php
/**
 * Contains the edit-user-action
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
 * The edit-user-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_user_edit extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		$cache = FWS_Props::get()->cache();
		$msgs = FWS_Props::get()->msgs();
		$user = FWS_Props::get()->user();
		$com = BS_Community_Manager::get_instance();

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';
		
		$data = BS_DAO::get_profile()->get_user_by_id($id,1,-1);
		if($data === false)
			return 'No user with id="'.$id.'" found';
		
		if($com->is_user_management_enabled())
		{
			$user_name = $input->get_var('user_name','post',FWS_Input::STRING);
			// check username
			if(!BS_UserUtils::check_username($user_name))
				return 'usernamenotallowed';

			if($functions->is_banned('user',$user_name))
				return 'user_name_banned';

			if(BS_DAO::get_user()->name_exists($user_name,$id))
				return 'registeruservorhanden';

			$user_pw = $input->get_var('user_pw','post',FWS_Input::STRING);
			$user_pw_conf = $input->get_var('user_pw_conf','post',FWS_Input::STRING);
			$user_email = $input->get_var('user_email','post',FWS_Input::STRING);

			// check inputs
			if($user_pw != '' && $user_pw !== $user_pw_conf)
				return 'registerpwsnichtidentisch';
			// ensure that both are empty if they are not equal
			if($user_pw !== $user_pw_conf)
				$user_pw = $user_pw_conf = '';

			// check email
			$user_email = trim($user_email);
			if(!FWS_StringHelper::is_valid_email($user_email))
				return 'mailnotallowed';

			if($functions->is_banned('mail',$user_email))
				return 'email_is_banned';

			// does the email already exist?
			if(BS_DAO::get_user()->email_exists($user_email,$id))
				return 'email_exists';
		}
		$main_group = $input->get_var('main_group','post',FWS_Input::ID);
		$other_groups = $input->get_var('other_groups','post');
		$post_text = $input->get_var('text','post',FWS_Input::STRING);
		$notify = $input->get_var('notify','post',FWS_Input::INT_BOOL);
		$remove_avatar = $input->get_var('remove_avatar','post',FWS_Input::INT_BOOL);

		$text = '';
		$error = BS_PostingUtils::prepare_message_for_db($text,$post_text,'sig');
		if($error != '')
			return $error;
		
		$sql_fields = array();
		
		// grab additional-field-data
		$cfields = BS_AddField_Manager::get_instance();
		$fields = $cfields->get_fields_at(
			BS_UF_LOC_POSTS | BS_UF_LOC_REGISTRATION | BS_UF_LOC_USER_DETAILS | BS_UF_LOC_USER_PROFILE
		);
		foreach($fields as $field)
		{
			$fdata = $field->get_data();
			$value = $field->get_value_from_formular();
			
			// admins may leave required fields empty
			if(!$field->is_empty($value) && ($error = $field->is_valid_value($value)) !== '')
				return sprintf($locale->lang('error_add_field_'.$error),$fdata->get_title());
			
			$sql_val = $field->get_value_to_store($value);
			$sql_fields['add_'.$fdata->get_name()] = $sql_val;
		}

		// update the database
		if($com->is_user_management_enabled())
			BS_DAO::get_user()->update($id,$user_name,$user_pw != '' ? BS_Password::hash($user_pw) : '',$user_email);

		$groups = array();
		if($user->get_user_id() == $id)
			$groups[] = (int)$data['user_group'];
		else
		{
			$gdata = $cache->get_cache('user_groups')->get_element($main_group);
			if($gdata === null)
				return 'The group "'.$main_group.'" doesn\'t exist!';
			if($gdata['is_visible'] == 0)
				return 'You can\'t choose invisible groups as main-group!';
			
			$groups[] = $main_group;
		}

		if(is_array($other_groups))
		{
			foreach($other_groups as $gid)
			{
				if($cache->get_cache('user_groups')->key_exists($gid))
					$groups[] = $gid;
			}
		}
		$groups = array_unique($groups);

		// remove avatar?
		if($remove_avatar && $data['avatar'] > 0)
		{
			$sql_fields['avatar'] = 0;

			$avdata = BS_DAO::get_avatars()->get_by_id($data['avatar']);
			if($avdata !== false)
				@unlink(FWS_Path::server_app().'images/avatars/'.$avdata['av_pfad']);

			BS_DAO::get_avatars()->delete_by_ids(array($data['avatar']));
		}

		$sql_fields['user_group'] = implode(',',$groups).',';
		$sql_fields['signatur'] = $text;
		$sql_fields['signature_posted'] = $post_text;
		BS_DAO::get_profile()->update_user_by_id($sql_fields,$id);
		
		// fire community-event
		$status = BS_Community_User::get_status_from_groups($groups);
		$u = new BS_Community_User(
			$id,$input->unescape_value($user_name,'post'),
			$input->unescape_value($user_email,'post'),$status,BS_Password::hash($user_pw),
			$input->unescape_value($user_pw,'post')
		);
		BS_Community_Manager::get_instance()->fire_user_changed($u);
		
		// send email if required
		if($notify == 1)
		{
			$email = BS_EmailFactory::get_instance()->get_account_changed_mail(
				$data['forum_lang'],$data['user_name'],$user_name,$data['user_email'],$user_email,$user_pw
			);
			if(!$email->send_mail())
			{
				$msg = $email->get_error_message();
				$msgs->add_error(sprintf($locale->lang('email_send_error'),$msg));
			}
		}
		
		$this->set_success_msg($locale->lang('edit_user_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>