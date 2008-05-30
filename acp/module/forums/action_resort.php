<?php
/**
 * Contains the resort-forums-action
 *
 * @version			$Id: action_resort.php 741 2008-05-24 12:04:56Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The resort-forums-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_forums_resort extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$this->_resort_forums(0);
		
		// refresh forums
		PLIB_Object::set_prop('forums',new BS_Forums_Manager());
		
		$this->set_success_msg($this->locale->lang('sort_successfully_corrected'));
		$this->set_action_performed(true);

		return '';
	}
	
	/**
	 * The recursive method to resort the forums
	 *
	 * @param int $parent_id the parent-id
	 */
	private function _resort_forums($parent_id)
	{
		$sub_forums = $this->forums->get_direct_sub_nodes($parent_id);
		if($sub_forums != null)
		{
			$i = 1;
			foreach($sub_forums as $node)
			{
				/* @var $node PLIB_Tree_Node */
				$data = $node->get_data();
				
				BS_DAO::get_forums()->update_sort($data->get_id(),$i);

				if($node->get_child_count() > 0)
					$this->_resort_forums($data->get_id());

				$i++;
			}
		}
	}
}
?>