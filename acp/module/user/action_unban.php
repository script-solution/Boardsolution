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
		$input = PLIB_Props::get()->input();
		$msgs = PLIB_Props::get()->msgs();
		$locale = PLIB_Props::get()->locale();

		$idstr = $input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';

		// reactivate the user
		BS_DAO::get_profile()->update_users_by_ids(array('banned' => 0),$ids);

		// send the emails and collect errors
		$email = BS_EmailFactory::get_instance()->get_account_reactivated_mail();
		$error_msgs = array();
		foreach(BS_DAO::get_profile()->get_users_by_ids($ids) as $data)
		{
			// fire community-event
			$user = BS_Community_User::get_instance_from_data($data);
			BS_Community_Manager::get_instance()->fire_user_reactivated($user);
			
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
			$msgs->add_error($msg);
	
		$this->set_success_msg($locale->lang('user_reactivated_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>