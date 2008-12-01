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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_RUN_TASK,'run');
		$renderer->add_action(BS_ACP_ACTION_DELETE_TASKS,'delete');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();

		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$names = $cache->get_cache('tasks')->get_field_vals_of_keys($ids,'task_title');
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpsub_url();
			$url->set('at',BS_ACP_ACTION_DELETE_TASKS);
			$url->set('ids',implode(',',$ids));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->to_url(),
				BS_URL::build_acpsub_url()
			);
		}
		
		$helper = BS_ACP_Module_Tasks_Helper::get_instance();
		$options = $helper->get_interval_types();
		$this->request_formular();
		
		$tpltasks = array();
		foreach($cache->get_cache('tasks') as $task)
		{
			$tidata = $helper->decode_interval($task['task_interval']);
			if($task['last_execution'] > 0)
			{
				$date = new FWS_Date($task['last_execution'],FWS_Date::TZ_GMT,FWS_Date::TZ_GMT);
				$last_run = $date->to_date(true,false).' GMT';
			}
			else
				$last_run = $locale->lang('notavailable');
			
			if($locale->contains_lang('task_desc_'.$task['task_title']))
			{
				if($task['task_title'] == 'subscriptions')
					$desc = sprintf($locale->lang('task_desc_'.$task['task_title']),BS_SUBSCRIPTION_TIMEOUT);
				else
					$desc = $locale->lang('task_desc_'.$task['task_title']);
			}
			else
				$desc = '';
			
			$tpltasks[] = array(
				'id' => $task['id'],
				'is_def' => $helper->is_default_task($task['task_file']),
				'title' => $locale->lang('task_'.$task['task_title'],false),
				'file' => FWS_Path::server_app().'src/tasks/'.$task['task_file'],
				'description' => $desc,
				'interval' => $tidata[0].' '.$options[$tidata[1]],
				'point_of_time' => $task['task_time'] !== null ? $task['task_time'].' GMT' : '-',
				'enabled' => $task['enabled'],
				'last_run' => $last_run
			);
		}
		
		$tpl->add_array('tasks',$tpltasks);
		$tpl->add_variables(array(
			'at_run' => BS_ACP_ACTION_RUN_TASK
		));
	}
}
?>