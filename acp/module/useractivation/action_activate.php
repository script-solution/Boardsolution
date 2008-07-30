<?php
/**
 * Contains the activate-useractivation-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The activate-useractivation-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_useractivation_activate extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		if(BS_ENABLE_EXPORT)
			return 'The community is exported';
		
		$idstr = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';
		
		// activate user
		BS_DAO::get_profile()->update_users_by_ids(array('active' => 1),$ids);
		
		// delete from activation-table
		BS_DAO::get_activation()->delete_by_users($ids);
		
		// fire community-event
		foreach(BS_DAO::get_profile()->get_users_by_ids($ids) as $data)
		{
			$user = BS_Community_User::get_instance_from_data($data);
			BS_Community_Manager::get_instance()->fire_user_registered($user);
		}

		// send emails
		BS_ACP_Utils::get_instance()->send_email_to_user(
			BS_EmailFactory::get_instance()->get_account_activated_mail(),
			$ids
		);
		
		$this->set_success_msg($locale->lang('accounts_activated_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>