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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_TASK,array('edit','edit'));
		
		$id = $input->get_var('id','get',FWS_Input::ID);
		$url = BS_URL::get_acpsub_url();
		$url->set('id',$id);
		$renderer->add_breadcrumb($locale->lang('edit_task'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$id = $input->get_var('id','get',FWS_Input::ID);
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
		
		$iv = BS_ACP_Module_Tasks_Helper::decode_interval($data['task_interval']);
		list($data['interval'],$data['interval_type']) = $iv;
		
		if($data['task_time'] == null)
			$data['time_hour'] = $data['time_min'] = $data['time_sec'] = '';
		else
			list($data['time_hour'],$data['time_min'],$data['time_sec']) = explode(':',$data['task_time']);
		
		$url = BS_URL::get_acpsub_url();
		$url->set('id',$id);
		$tpl->add_variables(array(
			'title' => $locale->lang('edit_task'),
			'is_def' => BS_ACP_Module_Tasks_Helper::is_default_task($data['task_file']),
			'action_type' => BS_ACP_ACTION_EDIT_TASK,
			'form_target' => $url->to_url(),
			'default' => $data,
			'interval_types' => BS_ACP_Module_Tasks_Helper::get_interval_types()
		));
		
		$this->request_formular();
	}
}
?>