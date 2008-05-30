<?php
/**
 * Contains the add-submodule for tasks
 * 
 * @version			$Id: sub_add.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add sub-module for the tasks-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_tasks_add extends BS_ACP_SubModule
{
	public function get_template()
	{
		return 'tasks_edit.htm';
	}
	
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_ADD_TASK => array('edit','add')
		);
	}
	
	public function run()
	{
		$data = array(
			'task_title' => '',
			'task_file' => '',
			'interval' => 7,
			'interval_type' => 'days',
			'enabled' => 1,
			'time_hour' => '',
			'time_min' => '',
			'time_sec' => ''
		);
		
		$this->tpl->add_variables(array(
			'title' => $this->locale->lang('add_task'),
			'is_def' => false,
			'action_type' => BS_ACP_ACTION_ADD_TASK,
			'form_target' => $this->url->get_acpmod_url(0,'&amp;action=add'),
			'default' => $data,
			'interval_types' => BS_ACP_Module_Tasks_Helper::get_instance()->get_interval_types()
		));
		
		$this->_request_formular();
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('add_task') => $this->url->get_acpmod_url(0,'&amp;action=add')
		);
	}
}
?>