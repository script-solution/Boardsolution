<?php
/**
 * Contains the ban-user-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The ban-user-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_user_ban extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$idstr = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';

		// grab the users from the database
		$email = BS_EmailFactory::get_instance()->get_account_deactivated_mail();
		$existing_ids = array();
		foreach(BS_DAO::get_user()->get_users_by_ids($ids) as $data)
		{
			if($data['id'] != $this->user->get_user_id())
			{
				$email->add_bcc_recipient($data['user_email']);
				$existing_ids[] = $data['id'];
			}
		}

		// have we found any?
		$count = count($existing_ids);
		if($count == 0)
			return 'No valid users found (do you want to ban yourself? ;))';
		
		// now deactivate them
		BS_DAO::get_profile()->update_users_by_ids(array('banned' => 1),$existing_ids);
		
		// send the email to them
		if(!$email->send_mail())
		{
			$msg = $email->get_error_message();
			$this->msgs->add_error(sprintf($this->locale->lang('email_send_error'),$msg));
		}
		
		$this->set_success_msg($this->locale->lang('user_deactivated_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>