<?php
/**
 * Contains the change-password-action
 *
 * @version			$Id: action_default.php 760 2008-05-24 18:57:19Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The change-password-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_change_password_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// check if the user is allowed to do this
		if((BS_ENABLE_EXPORT && BS_EXPORT_SEND_PW_TYPE != 'enabled') || $this->user->is_loggedin())
			return 'Community exported and send-pw-type not enabled or not loggedin';

		$user_id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$user_key = $this->input->get_var(BS_URL_KW,'get',PLIB_Input::STRING);

		// check parameter
		if($user_id == null || $user_key == null)
			return 'Missing user_id or user_key from GET';

		// check if the entry exists
		if(!BS_DAO::get_changepw()->exists($user_id,$user_key))
			return 'Invalid user-id or user-key';

		// check new password
		$password = $this->input->get_var('password','post',PLIB_Input::STRING);
		$password_conf = $this->input->get_var('password_conf','post',PLIB_Input::STRING);

		if($password == null || $password_conf == null)
			return 'missing_password';

		if($password != $password_conf)
			return 'passwords_not_equal';

		// everything seems to be ok, so we can change the password

		// grab userdata from db for the password-function
		$userdata = BS_DAO::get_profile()->get_user_by_id($user_id);
		if($userdata === false)
			return 'The user with id "'.$user_id.'" doesn\'t exist!';
		
		// build the password with the community-export-function
		$dbpw = BS_Ex_get_stored_password($password,$userdata);
		BS_DAO::get_user()->update($user_id,'',$dbpw);

		// finally delete the entry in the change-password-table
		BS_DAO::get_changepw()->delete_by_user($user_id);

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('forumindex'),$this->functions->get_start_url());

		return '';
	}
}
?>