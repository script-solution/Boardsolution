<?php
/**
 * Contains the topics-task for the miscellaneous module
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
 * The task to refresh the topic-attributes
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Miscellaneous_Tasks_Topics extends FWS_Object implements FWS_Progress_Task
{
	/**
	 * @see FWS_Progress_Task::get_total_operations()
	 *
	 * @return int
	 */
	public function get_total_operations()
	{
		return BS_DAO::get_topics()->get_count();
	}

	/**
	 * @see FWS_Progress_Task::run()
	 *
	 * @param int $pos
	 * @param int $ops
	 */
	public function run($pos,$ops)
	{
		$main = array();
		
		foreach(BS_DAO::get_topics()->get_list($pos,$ops) as $data)
		{
			$posts = BS_DAO::get_posts()->get_count_in_topic($data['id']);
			if($data['moved_tid'] > 0)
			{
				$lastpost = BS_DAO::get_posts()->get_lastpost_data_in_topic($data['moved_tid']);
				$lastpost['id'] = 0;
				$lastpost['post_user'] = 0;
				$lastpost['post_an_user'] = null;
				
				$main_data = BS_DAO::get_topics()->get_original_data_of_shadow_topic($data['moved_tid']);
				$main['name'] = $main_data['name'];
				$main['symbol'] = $main_data['symbol'];
				$main['comallow'] = $main_data['comallow'];
				$main['important'] = $main_data['important'];
			}
			else
			{
				$lastpost = BS_DAO::get_posts()->get_lastpost_data_in_topic($data['id']);
				$main['name'] = $data['name'];
				$main['symbol'] = $data['symbol'];
				$main['comallow'] = $data['comallow'];
				$main['important'] = $data['important'];
			}
			
			$main['name'] = addslashes($main['name']);

			BS_DAO::get_topics()->update_properties($data['id'],$lastpost,max(0,$posts - 1),$main);
		}
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>