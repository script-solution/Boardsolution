<?php
/**
 * Contains the edit- and add-task-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';
		
		// valid id?
		if($type == 'edit')
		{
			$id = $input->get_var('id','get',PLIB_Input::ID);
			if($id == null)
				return 'Invalid id "'.$id.'"';
			
			// does the task exist?
			$task = $cache->get_cache('tasks')->get_element($id);
			if($task === null)
				return 'No task found with id "'.$id.'"';
		}
		
		$helper = BS_ACP_Module_Tasks_Helper::get_instance();
		
		// grab values from post
		$title = $input->get_var('task_title','post',PLIB_Input::STRING);
		$file = $input->get_var('task_file','post',PLIB_Input::STRING);
		$interval = $input->get_var('interval','post',PLIB_Input::INTEGER);
		$interval_type = $input->correct_var(
			'interval_type','post',PLIB_Input::STRING,array('days','hours','minutes'),'days'
		);
		$time_hour = $input->get_var('time_hour','post',PLIB_Input::INTEGER);
		$time_min = $input->get_var('time_min','post',PLIB_Input::INTEGER);
		$time_sec = $input->get_var('time_sec','post',PLIB_Input::INTEGER);
		if($time_hour !== null && $time_min !== null && $time_sec !== null)
			$time = $time_hour.':'.$time_min.':'.$time_sec;
		else
			$time = null;
		$enabled = $input->get_var('enabled','post',PLIB_Input::INT_BOOL);
		
		$is_default = $type != 'add' && $helper->is_default_task($task['task_file']);
		
		// check the values
		if(!$is_default && trim($title) == '')
			return 'task_missing_title';
		
		$file = basename($file);
		if(!$is_default && !is_file(PLIB_Path::server_app().'src/tasks/'.$file))
			return 'task_invalid_file';
		
		// was it no default task but would be now?
		if(($type == 'add' || !$is_default) && $helper->is_default_task($file))
			return 'task_invalid_file';
		
		if($interval <= 0)
			return 'task_invalid_interval';
		
		// update / insert into the database
		if($type == 'add' || !$is_default)
		{
			$values = array(
				'task_title' => $title,
				'task_file' => $file,
				'task_interval' => $helper->encode_interval($interval,$interval_type),
				'task_time' => $time,
				'enabled' => $enabled
			);
		}
		else
		{
			$values = array(
				'task_interval' => $helper->encode_interval($interval,$interval_type),
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