<?php
/**
 * Contains the run-tasks-action
 *
 * @version			$Id: action_run.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';
		
		if($this->cache->get_cache('tasks')->get_element($id) === null)
			return 'No task found with id "'.$id.'"';
		
		$con = new BS_Tasks_Container();
		$con->run_task($id);
		
		$this->set_success_msg($this->locale->lang('task_run_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>