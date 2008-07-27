<?php
/**
 * Contains the switch-smileys-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The switch-smileys-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_smileys_switch extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();

		$ids = $input->get_var('ids','get',PLIB_Input::STRING);
		if(!($aids = PLIB_StringHelper::get_ids($ids)) || count($aids) != 2)
			return 'Got an invalid id-string via GET (need 2 ids)';
		
		list($id1,$id2) = $aids;
		$data1 = BS_DAO::get_smileys()->get_by_id($id1);
		$data2 = BS_DAO::get_smileys()->get_by_id($id2);
		if($data1 === null || $data2 === null)
			return 'At least one of the ids "'.$id1.'","'.$id2.'" doesn\'t exist';
		
		BS_DAO::get_smileys()->update_sort($id1,false);
		BS_DAO::get_smileys()->update_sort($id2,true);
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>