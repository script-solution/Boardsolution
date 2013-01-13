<?php
/**
 * Contains the run-tasks-action
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
 * The run-tasks-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_tasks_run extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';
		
		if($cache->get_cache('tasks')->get_element($id) === null)
			return 'No task found with id "'.$id.'"';
		
		$con = new BS_Tasks_Container();
		$con->run_task($id);
		
		$this->set_success_msg($locale->lang('task_run_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>