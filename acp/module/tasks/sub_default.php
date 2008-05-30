<?php
/**
 * Contains the default-submodule for tasks
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the tasks-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_tasks_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_RUN_TASK => 'run',
			BS_ACP_ACTION_DELETE_TASKS => 'delete'
		);
	}
	
	public function run()
	{
		if($this->input->isset_var('delete','post'))
		{
			$ids = $this->input->get_var('delete','post');
			$names = $this->cache->get_cache('tasks')->get_field_vals_of_keys($ids,'task_title');
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_TASKS.'&amp;ids='.implode(',',$ids)
				),
				$this->url->get_acpmod_url()
			);
		}
		
		$helper = BS_ACP_Module_Tasks_Helper::get_instance();
		$options = $helper->get_interval_types();
		$this->_request_formular();
		
		$tpltasks = array();
		foreach($this->cache->get_cache('tasks') as $task)
		{
			$tidata = $helper->decode_interval($task['task_interval']);
			if($task['last_execution'] > 0)
			{
				$date = new PLIB_Date($task['last_execution'],PLIB_Date::TZ_GMT,PLIB_Date::TZ_GMT);
				$last_run = $date->to_date(true,false).' GMT';
			}
			else
				$last_run = $this->locale->lang('notavailable');
			
			if($this->locale->contains_lang('task_desc_'.$task['task_title']))
			{
				if($task['task_title'] == 'subscriptions')
					$desc = sprintf($this->locale->lang('task_desc_'.$task['task_title']),BS_SUBSCRIPTION_TIMEOUT);
				else
					$desc = $this->locale->lang('task_desc_'.$task['task_title']);
			}
			else
				$desc = '';
			
			$tpltasks[] = array(
				'id' => $task['id'],
				'is_def' => $helper->is_default_task($task['task_file']),
				'title' => $this->locale->lang('task_'.$task['task_title'],false),
				'file' => PLIB_Path::inner().'src/tasks/'.$task['task_file'],
				'description' => $desc,
				'interval' => $tidata[0].' '.$options[$tidata[1]],
				'point_of_time' => $task['task_time'] !== null ? $task['task_time'].' GMT' : '-',
				'enabled' => $task['enabled'],
				'last_run' => $last_run
			);
		}
		
		$this->tpl->add_array('tasks',$tpltasks);
		$this->tpl->add_variables(array(
			'at_run' => BS_ACP_ACTION_RUN_TASK
		));
	}
	
	public function get_location()
	{
		return array();
	}
}
?>