<?php
/**
 * Contains the add-user-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		if(BS_ENABLE_EXPORT)
			return 'The community is exported';
		
		// check username
		$user_name = $this->input->get_var('user_name','post',PLIB_Input::STRING);
		if(trim($user_name) == '' || !BS_UserUtils::get_instance()->check_username($user_name))
			return 'usernamenotallowed';

		if($this->functions->is_banned('user',$user_name))
			return 'user_name_banned';

		if(BS_DAO::get_user()->name_exists($user_name))
			return 'registeruservorhanden';

		$user_pw = $this->input->get_var('user_pw','post',PLIB_Input::STRING);
		$user_pw_conf = $this->input->get_var('user_pw_conf','post',PLIB_Input::STRING);
		$user_email = $this->input->get_var('user_email','post',PLIB_Input::STRING);
		$main_group = $this->input->get_var('main_group','post',PLIB_Input::ID);
		$other_groups = $this->input->get_var('other_groups','post');
		$notify = $this->input->get_var('notify','post',PLIB_Input::INT_BOOL);

		// check pw
		if($user_pw == '' || $user_pw != $user_pw_conf)
			return 'registerpwsnichtidentisch';

		// check email
		$user_email = trim($user_email);
		if(!PLIB_StringHelper::is_valid_email($user_email))
			return 'mailnotallowed';

		if($this->functions->is_banned('mail',$user_email))
			return 'email_is_banned';

		// does the email already exist?
		if(BS_DAO::get_user()->email_exists($user_email))
			return 'email_exists';

		// create user
		$id = BS_DAO::get_user()->create($user_name,$user_email,$user_pw);

		// build user-groups
		$groups = array();
		if($this->cache->get_cache('user_groups')->key_exists($main_group))
			$groups[] = $main_group;

		if(is_array($other_groups))
		{
			foreach($other_groups as $gid)
			{
				if($this->cache->get_cache('user_groups')->key_exists($gid))
					$groups[] = $gid;
			}
		}
		$groups = array_unique($groups);
		
		// create profile-entry
		BS_DAO::get_profile()->create($id,$groups);

		// send email if required
		if($notify == 1)
		{
			$email = BS_EmailFactory::get_instance()->get_new_registration_mail(
				$user_name,$user_email,$user_pw
			);
			if(!$email->send_mail())
			{
				$msg = $email->get_error_message();
				$this->msgs->add_error(sprintf($this->locale->lang('email_send_error'),$msg));
			}
		}
		
		$this->set_success_msg($this->locale->lang('add_user_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>