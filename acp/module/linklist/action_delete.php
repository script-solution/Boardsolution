<?php
/**
 * Contains the delete-links-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-links-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_linklist_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();

		$id_str = $input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		BS_DAO::get_links()->delete_by_ids($ids);
		BS_DAO::get_linkvotes()->delete_by_links($ids);
		
		$this->set_action_performed(true);
		$this->set_success_msg($locale->lang('links_delete_successfully'));

		return '';
	}
}
?>