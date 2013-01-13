<?php
/**
 * Contains the registration-task
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
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
 * The task which deletes timed out registrations
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_registrations extends FWS_Tasks_Base
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