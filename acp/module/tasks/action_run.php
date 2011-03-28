<?php
/**
 * Contains the run-tasks-action
 *
 * @version			$Id$
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