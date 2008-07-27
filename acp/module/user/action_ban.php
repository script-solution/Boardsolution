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
		$input = PLIB_Props::get()->input();
		$msgs = PLIB_Props::get()->msgs();
		$locale = PLIB_Props::get()->locale();
		$user = PLIB_Props::get()->user();

		$idstr = $input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';

		$userdatas = array();
		
		// grab the users from the database
		$email = BS_EmailFactory::get_instance()->get_account_deactivated_mail();
		$existing_ids = array();
		foreach(BS_DAO::get_profile()->get_users_by_ids($ids) as $data)
		{
			if($data['id'] != $user->get_user_id())
			{
				$email->add_bcc_recipient($data['user_email']);
				$existing_ids[] = $data['id'];
				$userdatas[] = $data;
			}
		}

		// have we found any?
		$count = count($existing_ids);
		if($count == 0)
			return 'No valid users found (do you want to ban yourself? ;))';
		
		// now deactivate them
		BS_DAO::get_profile()->update_users_by_ids(array('banned' => 1),$existing_ids);
		
		// fire community-event
		foreach($userdatas as $data)
		{
			$u = BS_Community_User::get_instance_from_data($data);
			BS_Community_Manager::get_instance()->fire_user_deactivated($u);
		}
		
		// send the email to them
		if(!$email->send_mail())
		{
			$msg = $email->get_error_message();
			$msgs->add_error(sprintf($locale->lang('email_send_error'),$msg));
		}
		
		$this->set_success_msg($locale->lang('user_deactivated_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>