<?php
/**
 * Contains the topics-task for the miscellaneous module
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The task to refresh the topic-attributes
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Miscellaneous_Tasks_Topics extends PLIB_Object implements PLIB_Progress_Task
{
	/**
	 * @see PLIB_Progress_Task::get_total_operations()
	 *
	 * @return int
	 */
	public function get_total_operations()
	{
		return BS_DAO::get_topics()->get_count();
	}

	/**
	 * @see PLIB_Progress_Task::run()
	 *
	 * @param int $pos
	 * @param int $ops
	 */
	public function run($pos,$ops)
	{
		foreach(BS_DAO::get_topics()->get_list($pos,$ops) as $data)
		{
			$posts = BS_DAO::get_posts()->get_count_in_topic($data['id']);
			$lastpost = BS_DAO::get_posts()->get_lastpost_data_in_topic($data['id']);
			
			BS_DAO::get_topics()->update_properties($data['id'],$lastpost,$posts - 1);
		}
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>