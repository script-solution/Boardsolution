<?php
/**
 * Contains the operation-submodule for miscellaneous
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

// we remove the timelimit here for the case that a server is very slow and has a low time-limit
// this should never happen because of the "split-concept" but perhaps it does :)
@set_time_limit(0);

/**
 * The operation sub-module for the miscellaneous-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_miscellaneous_operation extends BS_ACP_SubModule
	implements FWS_Progress_Listener
{
	/**
	 * The progress-manager
	 *
	 * @var FWS_Progress_Manager
	 */
	private $_pm;
	
	/**
	 * The name of the task to execute
	 *
	 * @var string
	 */
	private $_name;
	
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
		
		$renderer->add_breadcrumb($locale->lang('miscellaneous_in_progress'));
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();

		$storage = new FWS_Progress_Storage_Session('misc_');
		$this->_pm = new FWS_Progress_Manager($storage);
		$this->_pm->set_ops_per_cycle(BS_MM_OPERATIONS_PER_CYCLE);
		$this->_pm->add_listener($this);
		
		if(!$this->_pm->is_running())
		{
			$refresh = $input->get_var('refresh','post');
			$this->_name = key($refresh);
		}
		else
			$this->_name = $input->get_var('name','get',FWS_Input::STRING);
		
		$tasks = BS_ACP_Module_miscellaneous::get_tasks();
		if(!isset($tasks[$this->_name]))
			FWS_Helper::error('The task "'.$this->_name.'" does not exist!');
		
		$file = FWS_Path::server_app().'acp/module/miscellaneous/tasks/'.$this->_name.'.php';
		if(is_file($file))
		{
			include_once($file);
			$class = 'BS_ACP_Miscellaneous_Tasks_'.$this->_name;
			if(class_exists($class))
			{
				$task = new $class();
				$this->_pm->run_task($task);
				return;
			}
		}
		
		FWS_Helper::error('The file or class for the task "'.$this->_name.'" does not exist!');
	}

	/**
	 * @see FWS_Progress_Listener::cycle_finished()
	 *
	 * @param int $pos
	 * @param int $total
	 */
	public function cycle_finished($pos,$total)
	{
		$this->_populate_template();
	}

	/**
	 * @see FWS_Progress_Listener::progress_finished()
	 */
	public function progress_finished()
	{
		$locale = FWS_Props::get()->locale();
		$msgs = FWS_Props::get()->msgs();
		$msg = $locale->lang('maintenance_misc_success');
		$msg .= '<p align="center" class="a_block_pad"><a href="'.BS_URL::build_acpmod_url().'">';
		$msg .= $locale->lang('back').'</a></p>';
		$msgs->add_message($msg);
		
		$this->_populate_template();
	}
	
	/**
	 * Adds the variables to the template
	 */
	private function _populate_template()
	{
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$tasks = BS_ACP_Module_miscellaneous::get_tasks();
		
		$url = BS_URL::get_acpsub_url(0,0,'&');
		$url->set('name',$this->_name);
		
		$tpl->add_variables(array(
			'not_finished' => !$this->_pm->is_finished(),
			'title' => sprintf(
				$locale->lang('mm_action_in_progress'),$locale->lang('title_'.$tasks[$this->_name])
			),
			'percent' => round($this->_pm->get_percentage(),1),
			'target_url' => $url->to_url()
		));
	}
}
?>