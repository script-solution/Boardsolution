<?php
/**
 * Contains the edit-user-action
 *
 * @version			$Id: action_edit.php 717 2008-05-21 14:12:53Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';
		
		$data = BS_DAO::get_profile()->get_user_by_id($id,1,-1);
		if($data === false)
			return 'No user with id="'.$id.'" found';
		
		if(!BS_ENABLE_EXPORT)
		{
			$user_name = $this->input->get_var('user_name','post',PLIB_Input::STRING);
			// check username
			if(!BS_UserUtils::get_instance()->check_username($user_name))
				return 'usernamenotallowed';

			if($this->functions->is_banned('user',$user_name))
				return 'user_name_banned';

			if(BS_DAO::get_user()->name_exists($user_name,$id))
				return 'registeruservorhanden';

			$user_pw = $this->input->get_var('user_pw','post',PLIB_Input::STRING);
			$user_pw_conf = $this->input->get_var('user_pw_conf','post',PLIB_Input::STRING);
			$user_email = $this->input->get_var('user_email','post',PLIB_Input::STRING);

			// check inputs
			if($user_pw != '' && $user_pw != $user_pw_conf)
				return 'registerpwsnichtidentisch';

			// check email
			$user_email = trim($user_email);
			if(!PLIB_StringHelper::is_valid_email($user_email))
				return 'mailnotallowed';

			if($this->functions->is_banned('mail',$user_email))
				return 'email_is_banned';

			// does the email already exist?
			if(BS_DAO::get_user()->email_exists($user_email,$id))
				return 'email_exists';
		}
		$main_group = $this->input->get_var('main_group','post',PLIB_Input::ID);
		$other_groups = $this->input->get_var('other_groups','post');
		$post_text = $this->input->get_var('text','post',PLIB_Input::STRING);
		$notify = $this->input->get_var('notify','post',PLIB_Input::INT_BOOL);
		$remove_avatar = $this->input->get_var('remove_avatar','post',PLIB_Input::INT_BOOL);

		$text = '';
		$error = BS_PostingUtils::get_instance()->prepare_message_for_db($text,$post_text,'sig');
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
				return sprintf($this->locale->lang('error_add_field_'.$error),$fdata->get_title());
			
			$sql_val = $field->get_value_to_store($value);
			$sql_fields['add_'.$fdata->get_name()] = $sql_val;
		}

		// update the database
		if(!BS_ENABLE_EXPORT)
			BS_DAO::get_user()->update($id,$user_name,$user_pw,$user_email);

		$groups = array();
		if($this->user->get_user_id() == $id)
			$groups[] = (int)$data['user_group'];
		else
		{
			$gdata = $this->cache->get_cache('user_groups')->get_element($main_group);
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
				if($this->cache->get_cache('user_groups')->key_exists($gid))
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
				@unlink(PLIB_Path::inner().'images/avatars/'.$avdata['av_pfad']);

			BS_DAO::get_avatars()->delete_by_ids(array($data['avatar']));
		}

		$sql_fields['user_group'] = implode(',',$groups).',';
		$sql_fields['signatur'] = $text;
		$sql_fields['signature_posted'] = $post_text;
		BS_DAO::get_profile()->update_user_by_id($sql_fields,$id);

		// send email if required
		if($notify == 1)
		{
			$lang_data = $this->cache->get_cache('languages')->get_element($data['forum_lang']);
			if($lang_data != null)
				$this->locale->add_language_file('email',$lang_data['lang_folder']);
			else
				$this->locale->add_language_file('email',$this->cfg['lang_folder']);

			$text = sprintf(
				$this->locale->lang('userdata_changed_email_text'),
				$this->cfg['forum_title'],
				($data['user_name'] != $user_name) ? $this->locale->lang('username').': '.$user_name."\n" : '',
				($user_pw != '') ? $this->locale->lang('password').': '.$user_pw."\n" : '',
				($data['user_email'] != $user_email) ? $this->locale->lang('email').': '.$user_email."\n" : ''
			);
			
			$title = sprintf(
				$this->locale->lang('userdata_changed_email_title'),
				$this->cfg['forum_title']
			);
			
			$email = $this->functions->get_mailer($data['user_email'],$title,$text);
			if(!$email->send_mail())
			{
				$msg = $email->get_error_message();
				$this->msgs->add_error(sprintf($this->locale->lang('email_send_error'),$msg));
			}
		}
		
		$this->set_success_msg($this->locale->lang('edit_user_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>