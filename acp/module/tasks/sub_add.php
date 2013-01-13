<?php
/**
 * Contains the add-submodule for tasks
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_ADD_TASK,array('edit','add'));
		$renderer->set_template('tasks_edit.htm');
		$renderer->add_breadcrumb($locale->lang('add_task'),BS_URL::build_acpsub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
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
			'form_target' => BS_URL::build_acpsub_url(),
			'default' => $data,
			'interval_types' => BS_ACP_Module_Tasks_Helper::get_interval_types()
		));
		
		$this->request_formular();
	}
}
?>