<?php
/**
 * Contains the sendpw-action
 *
 * @version			$Id: action_default.php 760 2008-05-24 18:57:19Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The sendpw-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_sendpw_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// check if the user is allowed to do this
		if((BS_ENABLE_EXPORT && BS_EXPORT_SEND_PW_TYPE != 'enabled') || $this->user->is_loggedin())
			return 'The community is exported and the send-pw-type is not enabled or you are a guest';

		$this->locale->add_language_file('email');

		$email_address = $this->input->get_var('email','post',PLIB_Input::STRING);

		if(!$this->functions->check_security_code())
			return 'invalid_security_code';

		// check if the email exists
		$data = BS_DAO::get_user()->get_user_by_email($email_address);
		if($data === false)
			return 'sendpw_invalid_email';
		
		// send the email
		$key = PLIB_StringHelper::generate_random_key();
		$url = $this->url->get_frontend_url(
			'&'.BS_URL_ACTION.'=change_password&'.BS_URL_ID.'='.$data['id'].'&'.BS_URL_KW.'='.$key,'&',false
		);
		$subject = sprintf($this->locale->lang('pw_change_title'),$this->cfg['forum_title']);
		$text = sprintf($this->locale->lang('pw_change_text'),$url);
		$email = $this->functions->get_mailer($email_address,$subject,$text);
		if(!$email->send_mail())
			return sprintf($this->locale->lang('error_mail_error'),$email->get_error_message());

		// create the entry / update the entry
		if(BS_DAO::get_changepw()->exists($data['id']))
			BS_DAO::get_changepw()->update_by_user($data['id'],$key);
		else
			BS_DAO::get_changepw()->create($data['id'],$key);

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('forumindex'),$this->functions->get_start_url());

		return '';
	}
}
?>