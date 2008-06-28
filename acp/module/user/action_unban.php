<?php
/**
 * Contains the unban-user-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The unban-user-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_user_unban extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$idstr = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';

		// reactivate the user
		BS_DAO::get_profile()->update_users_by_ids(array('banned' => 0),$ids);

		// build email
		$this->locale->add_language_file('email',$this->functions->get_def_lang_folder());
		$url = $this->url->get_frontend_url('','&',false);
		$msg = sprintf($this->locale->lang('account_reactivated_text'),$url);
		$email = $this->functions->get_mailer('',$this->locale->lang('account_reactivated_title'),$msg);

		// send the emails and collect errors
		$error_msgs = array();
		foreach(BS_DAO::get_user()->get_users_by_ids($ids) as $data)
		{
			$email->set_recipient($data['user_email']);
			if(!$email->send_mail())
			{
				$error = $email->get_error_message();
				if(!isset($error_msgs[$error]))
					$error_msgs[$error] = true;
			}
		}
		
		// add errors
		foreach(array_keys($error_msgs) as $msg)
			$this->msgs->add_error($msg);
		
		$this->set_success_msg($this->locale->lang('user_reactivated_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>