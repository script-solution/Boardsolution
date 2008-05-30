<?php
/**
 * Contains the delete-bots-action
 *
 * @version			$Id: action_delete.php 720 2008-05-21 14:44:38Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-bots-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_bots_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		$rows = BS_DAO::get_bots()->delete_by_ids($ids);
		if($rows > 0)
			$this->cache->refresh('bots');
		
		$this->set_success_msg($this->locale->lang('delete_bots_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>