<?php
/**
 * Contains the resort-forums-action
 *
 * @version			$Id$
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
		$locale = PLIB_Props::get()->locale();

		$this->_resort_forums(0);
		
		// refresh forums
		PLIB_Props::get()->reload('forums');
		
		$this->set_success_msg($locale->lang('sort_successfully_corrected'));
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
		$forums = PLIB_Props::get()->forums();

		$sub_forums = $forums->get_direct_sub_nodes($parent_id);
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