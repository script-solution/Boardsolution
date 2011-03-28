<?php
/**
 * Contains the update-infos-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-infos-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_updateinfos extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		$msgs = FWS_Props::get()->msgs();
		$user = FWS_Props::get()->user();
		$com = BS_Community_Manager::get_instance();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// the user has to be loggedin
		if(!$user->is_loggedin())
			return 'You are a guest';

		$email = '';
		if($com->is_user_management_enabled() && $cfg['allow_email_changes'])
		{
			$email = $input->get_var('user_email','post',FWS_Input::STRING);

			// email valid?
			$email = trim($email);
			if(!FWS_StringHelper::is_valid_email($email))
				return 'email_missing';

			// check if the email is banned
			if($functions->is_banned('mail',$email))
				return 'mailnotallowed';

			// does the email already exist?
			if(BS_DAO::get_user()->email_exists($email,$user->get_user_id()))
				return 'email_exists';
		}

		$sql_fields = array();

		// check and update additional-fields
		$cfields = BS_AddField_Manager::get_instance();
		foreach($cfields->get_fields_at(BS_UF_LOC_USER_PROFILE) as $field)
		{
			/* @var $field FWS_AddField_Field */
			$value = $field->get_value_from_formular();
			if(($error = $field->is_valid_value($value)) !== '')
				return sprintf($locale->lang('error_add_field_'.$error),$field->get_data()->get_title());
			
			$sql_val = $field->get_value_to_store($value);
			
			$fieldname = $field->get_data()->get_name();
			$sql_fields['add_'.$fieldname] = $sql_val;
			$user->set_profile_val('add_'.$fieldname,$sql_val);
		}

		// update the email-address
		if($com->is_user_management_enabled() && $cfg['allow_email_changes'] &&
			$email != $user->get_profile_val('user_email'))
		{
			// confirm it first?
			if($cfg['confirm_email_addresses'])
			{
				// delete potentially existing old entries for this user
				BS_DAO::get_changeemail()->delete_by_user($user->get_user_id());

				$key = FWS_StringHelper::generate_random_key();
				BS_DAO::get_changeemail()->create($user->get_user_id(),$key,$email);

				$uid = $user->get_user_id();
				$mail = BS_EmailFactory::get_instance()->get_change_email_mail($uid,$email,$key);
				if(!$mail->send_mail() && $mail->get_error_message())
					$msgs->add_error($mail->get_error_message());
			}
			else
			{
				BS_DAO::get_user()->update($user->get_user_id(),'',$email);
				
				$user->set_profile_val('email',$email);
				
				// fire community-event
				$status = BS_Community_User::get_status_from_groups($user->get_all_user_groups());
				$u = new BS_Community_User(
					$user->get_user_id(),$user->get_user_name(),
					$input->unescape_value($email,'post'),$status,$user->get_profile_val('user_pw')
				);
				BS_Community_Manager::get_instance()->fire_user_changed($u);
			}
		}

		// update other fields
		if(count($sql_fields) > 0)
			BS_DAO::get_profile()->update_user_by_id($sql_fields,$user->get_user_id());
		
		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),BS_URL::get_sub_url('userprofile','infos')
		);

		return '';
	}
}
?>