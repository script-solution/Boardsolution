<?php
/**
 * Contains the user-experience- and postcount-task for the miscellaneous module
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
 * The task to refresh the user-experience and postcount
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Miscellaneous_Tasks_UserExp extends FWS_Object implements FWS_Progress_Task
{
	/**
	 * @see FWS_Progress_Task::get_total_operations()
	 *
	 * @return int
	 */
	public function get_total_operations()
	{
		return BS_DAO::get_user()->get_user_count();
	}

	/**
	 * @see FWS_Progress_Task::run()
	 *
	 * @param int $pos
	 * @param int $ops
	 */
	public function run($pos,$ops)
	{
		// at the beginning we reset the posts and experience-points of all users
		if($pos == 0)
			BS_DAO::get_profile()->update_all(array('posts' => 0,'exppoints' => 0));
		
		// at first we have to collect the users for this cycle
		// because we have to make sure that we update _all_ data of a user in one cycle!
		$user_ids = array();
		foreach(BS_DAO::get_profile()->get_users('p.id','ASC',$pos,$ops,-1,-1) as $data)
			$user_ids[] = $data['id'];
		
		$user_data = array();
		
		// grab all posts of these users from the database
		foreach(BS_DAO::get_posts()->get_user_posts_of_users($user_ids) as $data)
		{
			if(!isset($user_data[$data['post_user']]['posts']))
				$user_data[$data['post_user']]['posts'] = 0;
			if($data['increase_postcount'] == 1)
				$user_data[$data['post_user']]['posts'] += $data['num'];

			if(!isset($user_data[$data['post_user']]['exp']))
				$user_data[$data['post_user']]['exp'] = 0;
			if($data['increase_experience'] == 1)
				$user_data[$data['post_user']]['exp'] += $data['num'] * BS_EXPERIENCE_FOR_POST;
		}

		// grab all topics of these users from the database
		foreach(BS_DAO::get_topics()->get_topics_of_users($user_ids) as $data)
		{
			if(!isset($user_data[$data['post_user']]['exp']))
				$user_data[$data['post_user']]['exp'] = 0;
			if(!isset($user_data[$data['post_user']]['posts']))
				$user_data[$data['post_user']]['posts'] = 0;

			$user_data[$data['post_user']]['exp'] += $data['topics'] * BS_EXPERIENCE_FOR_TOPIC;
		}

		// update the user-table
		foreach($user_data as $user_id => $stats)
		{
			BS_DAO::get_profile()->update_user_by_id(
				array(
					'posts' => array('posts + '.$stats['posts']),
					'exppoints' => array('exppoints + '.$stats['exp'])
				),
				$user_id
			);
		}
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>