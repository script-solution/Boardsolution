<?php
/**
 * Contains the delete-iplogs-action
 *
 * @version			$Id: action_delete.php 749 2008-05-24 15:33:31Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-iplogs-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_iplog_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		BS_DAO::get_logips()->delete_by_ids($ids);
		
		$this->set_success_msg($this->locale->lang('deleted_logs'));
		$this->set_action_performed(true);

		return '';
	}
}
?>