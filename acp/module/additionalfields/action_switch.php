<?php
/**
 * Contains the switch-addfields-action
 *
 * @version			$Id: action_switch.php 714 2008-05-20 22:14:58Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The switch-addfields-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_additionalfields_switch extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)) || count($ids) != 2)
			return 'Got an invalid id-string via GET';
		
		list($id1,$id2) = $ids;
		$first = BS_DAO::get_addfields()->get_by_id($id1);
		$second = BS_DAO::get_addfields()->get_by_id($id2);
		if(!$first['field_sort'])
			return 'A field with id "'.$id1.'" does not exist';
		if(!$second['field_sort'])
			return 'A field with id "'.$id2.'" does not exist';
		
		// update sort
		BS_DAO::get_addfields()->update_sort_by_id($second['field_sort'],$id1);
		BS_DAO::get_addfields()->update_sort_by_id($first['field_sort'],$id2);
		
		// refresh cache
		$this->cache->refresh('user_fields');
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>