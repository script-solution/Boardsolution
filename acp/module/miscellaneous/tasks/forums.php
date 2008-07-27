<?php
/**
 * Contains the forums-task for the miscellaneous module
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The task to refresh the forum-attributes
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Miscellaneous_Tasks_Forums extends PLIB_Object implements PLIB_Progress_Task
{
	/**
	 * @see PLIB_Progress_Task::get_total_operations()
	 *
	 * @return int
	 */
	public function get_total_operations()
	{
		return BS_DAO::get_forums()->get_count();
	}

	/**
	 * @see PLIB_Progress_Task::run()
	 *
	 * @param int $pos
	 * @param int $ops
	 */
	public function run($pos,$ops)
	{
		foreach(BS_DAO::get_forums()->get_list('id','ASC',$pos,$ops) as $data)
		{
			$posts = BS_DAO::get_posts()->get_count_in_forum($data['id']);
			$topics = BS_DAO::get_topics()->get_count_in_forum($data['id']);
			$lastpost = BS_DAO::get_posts()->get_lastpost_id_in_forum($data['id']);

			BS_DAO::get_forums()->update_by_id($data['id'],array(
				'posts' => $posts,
				'threads' => $topics,
				'lastpost_id' => $lastpost['id']
			));
		}
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>