<?php
/**
 * Contains the edit- and add-task-action
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
 * The edit- and add-task-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_tasks_edit extends BS_ACP_Action_Base
{
	public function perform_action($type = 'edit')
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';
		
		// valid id?
		if($type == 'edit')
		{
			$id = $input->get_var('id','get',FWS_Input::ID);
			if($id == null)
				return 'Invalid id "'.$id.'"';
			
			// does the task exist?
			$task = $cache->get_cache('tasks')->get_element($id);
			if($task === null)
				return 'No task found with id "'.$id.'"';
		}
		
		// grab values from post
		$title = $input->get_var('task_title','post',FWS_Input::STRING);
		$file = $input->get_var('task_file','post',FWS_Input::STRING);
		$interval = $input->get_var('interval','post',FWS_Input::INTEGER);
		$interval_type = $input->correct_var(
			'interval_type','post',FWS_Input::STRING,array('days','hours','minutes'),'days'
		);
		$time_hour = $input->get_var('time_hour','post',FWS_Input::INTEGER);
		$time_min = $input->get_var('time_min','post',FWS_Input::INTEGER);
		$time_sec = $input->get_var('time_sec','post',FWS_Input::INTEGER);
		if($time_hour !== null && $time_min !== null && $time_sec !== null)
			$time = $time_hour.':'.$time_min.':'.$time_sec;
		else
			$time = null;
		$enabled = $input->get_var('enabled','post',FWS_Input::INT_BOOL);
		
		$is_default = $type != 'add' && BS_ACP_Module_Tasks_Helper::is_default_task($task['task_file']);
		
		// check the values
		if(!$is_default && trim($title) == '')
			return 'task_missing_title';
		
		if($file !== null)
			$file = basename($file);
		if(!$is_default && !is_file(FWS_Path::server_app().'src/tasks/'.$file))
			return 'task_invalid_file';
		
		// was it no default task but would be now?
		if(($type == 'add' || !$is_default) && BS_ACP_Module_Tasks_Helper::is_default_task($file))
			return 'task_invalid_file';
		
		if($interval <= 0)
			return 'task_invalid_interval';
		
		// update / insert into the database
		if($type == 'add' || !$is_default)
		{
			$values = array(
				'task_title' => $title,
				'task_file' => $file,
				'task_interval' => BS_ACP_Module_Tasks_Helper::encode_interval($interval,$interval_type),
				'task_time' => $time,
				'enabled' => $enabled
			);
		}
		else
		{
			$values = array(
				'task_interval' => BS_ACP_Module_Tasks_Helper::encode_interval($interval,$interval_type),
				'task_time' => $time,
				'enabled' => $enabled
			);
		}
		
		if($type == 'edit')
			BS_DAO::get_tasks()->update_by_id($id,$values);
		else
		{
			$values['last_execution'] = 0;
			BS_DAO::get_tasks()->create($values);
		}
		
		// we have to refresh the cache
		$cache->refresh('tasks');
		
		$this->set_success_msg($locale->lang('task_'.$type.'_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>