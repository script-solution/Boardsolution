<?php
/**
 * Contains the operation-submodule for miscellaneous
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	implements PLIB_Progress_Listener
{
	/**
	 * The progress-manager
	 *
	 * @var PLIB_Progress_Manager
	 */
	private $_pm;
	
	/**
	 * The name of the task to execute
	 *
	 * @var string
	 */
	private $_name;
	
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_breadcrumb($locale->lang('miscellaneous_in_progress'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();

		$storage = new PLIB_Progress_Storage_Session('misc_');
		$this->_pm = new PLIB_Progress_Manager($storage);
		$this->_pm->set_ops_per_cycle(BS_MM_OPERATIONS_PER_CYCLE);
		$this->_pm->add_listener($this);
		
		if(!$this->_pm->is_running())
		{
			$refresh = $input->get_var('refresh','post');
			$this->_name = key($refresh);
		}
		else
			$this->_name = $input->get_var('name','get',PLIB_Input::STRING);
		
		$tasks = BS_ACP_Module_miscellaneous::get_tasks();
		if(!isset($tasks[$this->_name]))
			PLIB_Helper::error('The task "'.$this->_name.'" does not exist!');
		
		$file = PLIB_Path::server_app().'acp/module/miscellaneous/tasks/'.$this->_name.'.php';
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
		
		PLIB_Helper::error('The file or class for the task "'.$this->_name.'" does not exist!');
	}

	/**
	 * @see PLIB_Progress_Listener::cycle_finished()
	 *
	 * @param int $pos
	 * @param int $total
	 */
	public function cycle_finished($pos,$total)
	{
		$this->_populate_template();
	}

	/**
	 * @see PLIB_Progress_Listener::progress_finished()
	 */
	public function progress_finished()
	{
		$locale = PLIB_Props::get()->locale();
		$msgs = PLIB_Props::get()->msgs();
		$url = PLIB_Props::get()->url();

		$murl = $url->get_acpmod_url();
		$msg = $locale->lang('maintenance_misc_success');
		$msg .= '<p align="center" class="a_block_pad"><a href="'.$murl.'">';
		$msg .= $locale->lang('back').'</a></p>';
		$msgs->add_message($msg);
		
		$this->_populate_template();
	}
	
	/**
	 * Adds the variables to the template
	 */
	private function _populate_template()
	{
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		$tasks = BS_ACP_Module_miscellaneous::get_tasks();
		$tpl->add_variables(array(
			'not_finished' => !$this->_pm->is_finished(),
			'title' => sprintf(
				$locale->lang('mm_action_in_progress'),$locale->lang('title_'.$tasks[$this->_name])
			),
			'percent' => round($this->_pm->get_percentage(),1),
			'target_url' => $url->get_acpmod_url(0,'&action=operation&name='.$this->_name,'&')
		));
	}
}
?>