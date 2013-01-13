<?php
/**
 * Contains the tasks-db-storage-class
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
 * The db-based implementation of the task-storage
 *
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_Storage_DB extends FWS_Object implements FWS_Tasks_Storage
{
	/**
	 * Indicates wether multiple tasks will be executed. Additionally we count
	 * the number of executed tasks so we know wether we should update the cache
	 *
	 * @var int
	 */
	private $_multiple = 0;
	
	public function get_tasks()
	{
		$cache = FWS_Props::get()->cache();

		$res = array();
		foreach($cache->get_cache('tasks') as $task)
		{
			$res[] = new FWS_Tasks_Data(
				$task['id'],$task['task_file'],$task['task_interval'],new FWS_Date($task['last_execution']),
				$task['enabled'],$task['task_time']
			);
		}
		return $res;
	}
	
	/**
	 * This method will be called if multiple tasks may be run. After all tasks have been
	 * executed #finish() will be run.
	 * 
	 * @see finish()
	 */
	public function start()
	{
		$this->_multiple++;
	}
	
	/**
	 * Should store the given task.
	 *
	 * @param FWS_Tasks_Data $task the task to store
	 */
	public function store_task($task)
	{
		$cache = FWS_Props::get()->cache();

		$tasks = $cache->get_cache('tasks');
		$id = $task->get_id();
		$tasks->set_element_field($id,'task_interval',$task->get_interval());
		$tasks->set_element_field($id,'last_execution',$task->get_last_execution()->to_timestamp());
		$tasks->set_element_field($id,'task_time',$task->get_time());
		
		BS_DAO::get_tasks()->update_by_id($task->get_id(),array(
			'task_interval' => $task->get_interval(),
			'last_execution' => $task->get_last_execution()->to_timestamp(),
			'task_time' => $task->get_time()
		));
		
		if($this->_multiple == 0)
			$cache->store('tasks');
		else
			$this->_multiple++;
	}
	
	/**
	 * This method will be called if #start() has been called and all required tasks have
	 * been executed. For each task #store_task($task) will be called but if you have to
	 * perform some finishing operation you may do this here.
	 * 
	 * @see start()
	 */
	public function finish()
	{
		$cache = FWS_Props::get()->cache();

		if($this->_multiple > 1)
			$cache->store('tasks');
		$this->_multiple = 0;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>