<?php
/**
 * Contains the edit-submodule for tasks
 * 
 * @version			$Id: sub_edit.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit sub-module for the tasks-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_tasks_edit extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_EDIT_TASK => array('edit','edit')
		);
	}
	
	public function run()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$this->_report_error();
			return;
		}
		
		$tasks = $this->cache->get_cache('tasks');
		$data = $tasks->get_element($id);
		if($data === null)
		{
			$this->_report_error();
			return;
		}
		
		$helper = BS_ACP_Module_Tasks_Helper::get_instance();
		$iv = $helper->decode_interval($data['task_interval']);
		list($data['interval'],$data['interval_type']) = $iv;
		
		if($data['task_time'] == null)
			$data['time_hour'] = $data['time_min'] = $data['time_sec'] = '';
		else
			list($data['time_hour'],$data['time_min'],$data['time_sec']) = explode(':',$data['task_time']);
		
		$this->tpl->add_variables(array(
			'title' => $this->locale->lang('edit_task'),
			'is_def' => $helper->is_default_task($data['task_file']),
			'action_type' => BS_ACP_ACTION_EDIT_TASK,
			'form_target' => $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id),
			'default' => $data,
			'interval_types' => $helper->get_interval_types()
		));
		
		$this->_request_formular();
	}
	
	public function get_location()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		return array(
			$this->locale->lang('edit_task') => $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
}
?>