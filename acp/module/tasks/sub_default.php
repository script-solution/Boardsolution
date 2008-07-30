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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_RUN_TASK,'run');
		$renderer->add_action(BS_ACP_ACTION_DELETE_TASKS,'delete');
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$url = PLIB_Props::get()->url();
		$tpl = PLIB_Props::get()->tpl();

		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$names = $cache->get_cache('tasks')->get_field_vals_of_keys($ids,'task_title');
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_TASKS.'&amp;ids='.implode(',',$ids)
				),
				$url->get_acpmod_url()
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
				$date = new PLIB_Date($task['last_execution'],PLIB_Date::TZ_GMT,PLIB_Date::TZ_GMT);
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
				'file' => PLIB_Path::server_app().'src/tasks/'.$task['task_file'],
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