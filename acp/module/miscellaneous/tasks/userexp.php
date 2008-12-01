<?php
/**
 * Contains the user-experience-task for the miscellaneous module
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The task to refresh the user-experience
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
			$user_data[$data['post_user']]['posts'] += $data['posts'];

			if(!isset($user_data[$data['post_user']]['exp']))
				$user_data[$data['post_user']]['exp'] = 0;
			if($data['increase_experience'] == 1)
				$user_data[$data['post_user']]['exp'] += $data['posts'] * BS_EXPERIENCE_FOR_POST;
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
				array('posts' => 'posts + '.$stats['posts'],'exppoints' => 'exppoints + '.$stats['exp']),
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