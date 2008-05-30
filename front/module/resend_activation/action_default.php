<?php
/**
 * Contains the resend_activation-action
 *
 * @version			$Id: action_default.php 757 2008-05-24 18:32:30Z nasmussen $
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
		if($this->user->is_loggedin())
			return 'You are loggedin';

		if(BS_ENABLE_EXPORT)
			return 'The community is exported';

		if(!$this->functions->check_security_code())
			return 'invalid_security_code';

		$email = $this->input->get_var('email','post',PLIB_Input::STRING);
		if($email === null)
			return 'The email-address "'.$email.'" is invalid';

		$user = BS_DAO::get_profile()->get_user_by_email($email);
		if($user === false)
			return 'sendpw_invalid_email';

		if($user['active'] == 1)
			return 'sendpw_user_activated';
		
		$act = BS_DAO::get_activation()->get_by_user($user['id']);
		if($act === false)
			return 'No activation-entry found';
		
		$this->locale->add_language_file('email');

		// send the email
		$url = $this->url->get_standalone_url(
			'front','activate','&user_id='.$user['id'].'&user_key='.$act['user_key'],'&',true
		);
		$message = sprintf($this->locale->lang('account_activation_email_text'),
			$this->cfg['forum_title'],$url);
		$subject = $this->locale->lang('account_activation_email_title');

		$email = $this->functions->get_mailer($email,$subject,$message);
		if(!$email->send_mail())
			return sprintf($this->locale->lang('error_mail_error'),$email->get_error_message());

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('back'),$this->functions->get_start_url());

		return '';
	}
}
?>