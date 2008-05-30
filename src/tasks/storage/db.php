<?php
/**
 * Contains the tasks-db-storage-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The db-based implementation of the task-storage
 *
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_Storage_DB extends PLIB_FullObject implements PLIB_Tasks_Storage
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
		$res = array();
		foreach($this->cache->get_cache('tasks') as $task)
		{
			$res[] = new PLIB_Tasks_Data(
				$task['id'],$task['task_file'],$task['task_interval'],new PLIB_Date($task['last_execution']),
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
	 * @param PLIB_Tasks_Data $task the task to store
	 */
	public function store_task($task)
	{
		$tasks = $this->cache->get_cache('tasks');
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
			$this->cache->store('tasks');
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
		if($this->_multiple > 1)
			$this->cache->store('tasks');
		$this->_multiple = 0;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>