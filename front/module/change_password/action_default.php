<?php
/**
 * Contains the change-password-action
 *
 * @version			$Id$
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
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$com = BS_Community_Manager::get_instance();

		// check if the user is allowed to do this
		if(!$com->is_send_pw_enabled() || $user->is_loggedin())
			return 'Send-pw disabled or not loggedin';

		$user_id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$user_key = $input->get_var(BS_URL_KW,'get',FWS_Input::STRING);

		// check parameter
		if($user_id == null || $user_key == null)
			return 'Missing user_id or user_key from GET';

		// check if the entry exists
		if(!BS_DAO::get_changepw()->exists($user_id,$user_key))
			return 'Invalid user-id or user-key';

		// check new password
		$password = $input->get_var('password','post',FWS_Input::STRING);
		$password_conf = $input->get_var('password_conf','post',FWS_Input::STRING);

		if($password == null || $password_conf == null)
			return 'missing_password';

		if($password != $password_conf)
			return 'passwords_not_equal';

		// everything seems to be ok, so we can change the password

		// grab userdata from db for the password-function
		$userdata = BS_DAO::get_profile()->get_user_by_id($user_id);
		if($userdata === false)
			return 'The user with id "'.$user_id.'" doesn\'t exist!';
		
		BS_DAO::get_user()->update($user_id,'',md5($password));

		// finally delete the entry in the change-password-table
		BS_DAO::get_changepw()->delete_by_user($user_id);
		
		// fire community-event
		$groups = FWS_Array_Utils::advanced_explode(',',$userdata['user_group']);
		$status = BS_Community_User::get_status_from_groups($groups);
		$u = new BS_Community_User(
			$user_id,$userdata['user_name'],$userdata['user_email'],$status,$dbpw,
			$input->unescape_value($password,'post')
		);
		BS_Community_Manager::get_instance()->fire_user_changed($u);
		
		$this->set_action_performed(true);
		$this->add_link($locale->lang('forumindex'),BS_URL::get_start_url());

		return '';
	}
}
?>