<?php
/**
 * Contains the delete-tasks-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-tasks-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_tasks_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();

		$id_str = $input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		$helper = BS_ACP_Module_Tasks_Helper::get_instance();
		$del = array();
		$tasks = $cache->get_cache('tasks');
		foreach($ids as $id)
		{
			$task = $tasks->get_element($id);
			if($task !== null && !$helper->is_default_task($task['task_file']))
				$del[] = $id;
		}
		
		if(count($del) == 0)
			return 'Got no valid ids (the default tasks of BS can\'t be deleted!)';
		
		BS_DAO::get_tasks()->delete_by_ids($del);
		$cache->refresh('tasks');
		
		$this->set_success_msg($locale->lang('tasks_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>