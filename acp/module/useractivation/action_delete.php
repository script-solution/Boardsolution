<?php
/**
 * Contains the delete-useractivation-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-useractivation-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_useractivation_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();

		if(BS_ENABLE_EXPORT)
			return 'The community is exported';
		
		$idstr = $input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';
		
		// delete user
		BS_DAO::get_user()->delete($ids);
		BS_DAO::get_profile()->delete($ids);
		
		// delete from activation-table
		BS_DAO::get_activation()->delete_by_users($ids);
		
		// send email
		BS_ACP_Utils::get_instance()->send_email_to_user(
			BS_EmailFactory::get_instance()->get_account_not_activated_mail(),
			$ids
		);
		
		$this->set_success_msg($locale->lang('accounts_deleted_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>