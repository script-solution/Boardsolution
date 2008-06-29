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
		if(BS_ENABLE_EXPORT)
			return 'The community is exported';
		
		$idstr = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';
		
		// activate user
		BS_DAO::get_profile()->update_users_by_ids(array('active' => 1),$ids);
		
		// delete from activation-table
		BS_DAO::get_activation()->delete_by_users($ids);

		// send emails
		BS_ACP_Utils::get_instance()->send_email_to_user(
			BS_EmailFactory::get_instance()->get_account_activated_mail(),
			$ids
		);
		
		$this->set_success_msg($this->locale->lang('accounts_activated_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>