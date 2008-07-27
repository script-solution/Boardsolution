<?php
/**
 * Contains the add-submodule for tasks
 * 
 * @version			$Id$
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACP_ACTION_ADD_TASK,array('edit','add'));
		$doc->set_template('tasks_edit.htm');
		$doc->add_breadcrumb($locale->lang('add_task'),$url->get_acpmod_url(0,'&amp;action=add'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

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
		
		$tpl->add_variables(array(
			'title' => $locale->lang('add_task'),
			'is_def' => false,
			'action_type' => BS_ACP_ACTION_ADD_TASK,
			'form_target' => $url->get_acpmod_url(0,'&amp;action=add'),
			'default' => $data,
			'interval_types' => BS_ACP_Module_Tasks_Helper::get_instance()->get_interval_types()
		));
		
		$this->request_formular();
	}
}
?>