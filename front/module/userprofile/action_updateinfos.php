<?php
/**
 * Contains the update-infos-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-infos-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_updateinfos extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// nothing to do?
		if(!$this->input->isset_var('submit','post'))
			return '';

		// the user has to be loggedin
		if(!$this->user->is_loggedin())
			return 'You are a guest';

		$email = '';
		if(!BS_ENABLE_EXPORT && $this->cfg['allow_email_changes'])
		{
			$email = $this->input->get_var('user_email','post',PLIB_Input::STRING);

			// email valid?
			$email = trim($email);
			if(!PLIB_StringHelper::is_valid_email($email))
				return 'email_missing';

			// check if the email is banned
			if($this->functions->is_banned('mail',$email))
				return 'mailnotallowed';

			// does the email already exist?
			if(BS_DAO::get_user()->email_exists($email,$this->user->get_user_id()))
				return 'email_exists';
		}

		$sql_fields = array();

		// check and update additional-fields
		$cfields = BS_AddField_Manager::get_instance();
		foreach($cfields->get_fields_at(BS_UF_LOC_USER_PROFILE) as $field)
		{
			/* @var $field PLIB_AddField_Field */
			$value = $field->get_value_from_formular();
			if(($error = $field->is_valid_value($value)) !== '')
				return sprintf($this->locale->lang('error_add_field_'.$error),$field->get_data()->get_title());
			
			$sql_val = $field->get_value_to_store($value);
			
			$fieldname = $field->get_data()->get_name();
			$sql_fields['add_'.$fieldname] = $sql_val;
			$this->user->set_profile_val('add_'.$fieldname,$sql_val);
		}

		// update the email-address
		if(!BS_ENABLE_EXPORT && $this->cfg['allow_email_changes'] &&
			$email != $this->user->get_profile_val('user_email'))
		{
			// confirm it first?
			if($this->cfg['confirm_email_addresses'])
			{
				// delete potentially existing old entries for this user
				BS_DAO::get_changeemail()->delete_by_user($this->user->get_user_id());

				$key = PLIB_StringHelper::generate_random_key();
				BS_DAO::get_changeemail()->create($this->user->get_user_id(),$key,$email);

				$uid = $this->user->get_user_id();
				$mail = BS_EmailFactory::get_instance()->get_change_email_mail($uid,$email,$key);
				if(!$mail->send_mail())
					$this->msgs->add_error($mail->get_error_message());
			}
			else
			{
				BS_DAO::get_user()->update($this->user->get_user_id(),'',$email);
				
				$this->user->set_profile_val('email',$email);
			}
		}

		// update other fields
		if(count($sql_fields) > 0)
			BS_DAO::get_profile()->update_user_by_id($sql_fields,$this->user->get_user_id());
		
		$this->set_action_performed(true);
		$this->add_link(
			$this->locale->lang('back'),$this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=infos')
		);

		return '';
	}
}
?>