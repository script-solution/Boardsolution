<?php
/**
 * Contains the edit-submodule for tasks
 * 
 * @version			$Id$
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_TASK,array('edit','edit'));
		
		$id = $input->get_var('id','get',PLIB_Input::ID);
		$renderer->add_breadcrumb(
			$locale->lang('edit_task'),
			$url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		$id = $input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}
		
		$tasks = $cache->get_cache('tasks');
		$data = $tasks->get_element($id);
		if($data === null)
		{
			$this->report_error();
			return;
		}
		
		$helper = BS_ACP_Module_Tasks_Helper::get_instance();
		$iv = $helper->decode_interval($data['task_interval']);
		list($data['interval'],$data['interval_type']) = $iv;
		
		if($data['task_time'] == null)
			$data['time_hour'] = $data['time_min'] = $data['time_sec'] = '';
		else
			list($data['time_hour'],$data['time_min'],$data['time_sec']) = explode(':',$data['task_time']);
		
		$tpl->add_variables(array(
			'title' => $locale->lang('edit_task'),
			'is_def' => $helper->is_default_task($data['task_file']),
			'action_type' => BS_ACP_ACTION_EDIT_TASK,
			'form_target' => $url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id),
			'default' => $data,
			'interval_types' => $helper->get_interval_types()
		));
		
		$this->request_formular();
	}
}
?>