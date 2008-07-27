<?php
/**
 * Contains the switch-forums-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The switch-forums-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_forums_switch extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$forums = PLIB_Props::get()->forums();

		$id_str = $input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)) || count($ids) != 2)
			return 'Got an invalid id-string via GET';
		
		// check if the forums exist and have the same parent
		list($fid1,$fid2) = $ids;
		$data1 = $forums->get_node_data($fid1);
		$data2 = $forums->get_node_data($fid2);
		if($data1 === null || $data2 === null || $data1->get_parent_id() != $data2->get_parent_id())
			return 'Forums with ids "'.$fid1.'","'.$fid2.'" don\'t exist or are not in the same parent-forum';
		
		// update sort
		BS_DAO::get_forums()->update_sort($fid1,array('sortierung - 1'));
		BS_DAO::get_forums()->update_sort($fid2,array('sortierung + 1'));
		
		// refresh forums
		PLIB_Props::get()->reload('forums');
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>