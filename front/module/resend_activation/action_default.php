<?php
/**
 * Contains the resend_activation-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The resend_activation-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
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

		if($user->is_loggedin())
			return 'You are loggedin';

		if(BS_ENABLE_EXPORT)
			return 'The community is exported';

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