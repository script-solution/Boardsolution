<?php
/**
 * Contains the delete-useractivation-action
 *
 * @version			$Id: action_delete.php 757 2008-05-24 18:32:30Z nasmussen $
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
		if(BS_ENABLE_EXPORT)
			return 'The community is exported';
		
		$idstr = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';
		
		// delete user
		BS_DAO::get_user()->delete($ids);
		BS_DAO::get_profile()->delete($ids);
		
		// delete from activation-table
		BS_DAO::get_activation()->delete_by_users($ids);
		
		// send email
		$url = $this->url->get_frontend_url('','&',false);
		BS_ACP_Utils::get_instance()->send_email_to_user(
			$ids,$this->locale->lang('account_not_activated_title'),
			sprintf($this->locale->lang('account_not_activated_text'),$url)
		);
		
		$this->set_success_msg($this->locale->lang('accounts_deleted_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>