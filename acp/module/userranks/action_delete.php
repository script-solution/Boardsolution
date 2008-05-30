<?php
/**
 * Contains the delete-userranks-action
 *
 * @version			$Id: action_delete.php 759 2008-05-24 18:46:18Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-userranks-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_userranks_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		$rows = BS_DAO::get_ranks()->delete_by_ids($ids);
		if($rows > 0)
			$this->cache->refresh('user_ranks');
		
		$this->set_success_msg($this->locale->lang('user_ranks_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>