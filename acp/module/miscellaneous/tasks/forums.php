<?php
/**
 * Contains the forums-task for the miscellaneous module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * The task to refresh the forum-attributes
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Miscellaneous_Tasks_Forums extends FWS_Object implements FWS_Progress_Task
{
	/**
	 * @see FWS_Progress_Task::get_total_operations()
	 *
	 * @return int
	 */
	public function get_total_operations()
	{
		return BS_DAO::get_forums()->get_count();
	}

	/**
	 * @see FWS_Progress_Task::run()
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
				'lastpost_id' => $lastpost
			));
		}
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>