<?php
/**
 * Contains the sendpw-action
 *
 * @version			$Id$
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

		if(!$this->functions->check_security_code())
			return 'invalid_security_code';

		// check if the email exists
		$email_address = $this->input->get_var('email','post',PLIB_Input::STRING);
		$data = BS_DAO::get_user()->get_user_by_email($email_address);
		if($data === false)
			return 'sendpw_invalid_email';
		
		// send the email
		$key = PLIB_StringHelper::generate_random_key();
		$email = BS_EmailFactory::get_instance()->get_change_pw_mail($data['id'],$email_address,$key);
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