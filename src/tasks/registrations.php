<?php
/**
 * Contains the registration-task
 * 
 * @version			$Id: registrations.php 757 2008-05-24 18:32:30Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The task which deletes timed out registrations
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_registrations extends PLIB_Tasks_Base
{
	public function run()
	{
		// collect all user-ids to delete
		$user_ids = array();
		foreach(BS_DAO::get_activation()->get_timedout_entries(BS_DEAD_REG_DELETE_INTERVAL) as $data)
			$user_ids[] = $data['user_id'];
		
		// delete the entries
		if(count($user_ids) > 0)
		{
			BS_DAO::get_user()->delete($user_ids);
			BS_DAO::get_profile()->delete($user_ids);
			BS_DAO::get_activation()->delete_by_users($user_ids);
		}
	}
}
?>