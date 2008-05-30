<?php
/**
 * Contains the dba-progress-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * A progress-bar for the dbbackup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Progress extends PLIB_FullObject implements PLIB_Progress_Listener
{
	/**
	 * Clears the progress (ensures that the next progress starts at position 0)
	 */
	public static function clear_progress()
	{
		$storage = new PLIB_Progress_Storage_PHPSession('dba_');
		$storage->clear();
	}
	
	/**
	 * The progress-manager
	 *
	 * @var PLIB_Progress_Manager
	 */
	private $_pm;
	
	/**
	 * The title of the task
	 *
	 * @var string
	 */
	private $_title;
	
	/**
	 * The success-message
	 *
	 * @var string
	 */
	private $_success_msg;
	
	/**
	 * The next-URL
	 *
	 * @var string
	 */
	private $_next_url;
	
	/**
	 * The back-URL
	 *
	 * @var string
	 */
	private $_back_url;
	
	/**
	 * Constructor
	 * 
	 * @param string $title the title of the task
	 * @param string $success_msg the success-message
	 * @param string $next_url the URL to walk to the next step
	 * @param string $back_url to URL to walk back
	 * @param PLIB_Progress_Task $task the task to execute
	 * @param int $ops the number of ops per cycle
	 */
	public function __construct($title,$success_msg,$next_url,$back_url,$task,
		$ops = BS_DBA_OPERATIONS_PER_CYCLE)
	{
		parent::__construct();
		
		if(!($task instanceof PLIB_Progress_Task))
			PLIB_Helper::def_error('instance','task','PLIB_Progress_Task',$task);
		
		$this->_title = $title;
		$this->_success_msg = $success_msg;
		$this->_next_url = $next_url;
		$this->_back_url = $back_url;
		
		$storage = new PLIB_Progress_Storage_PHPSession('dba_');
		$this->_pm = new PLIB_Progress_Manager($storage);
		$this->_pm->set_ops_per_cycle($ops);
		$this->_pm->add_listener($this);
		$this->_pm->run_task($task);
	}
	
	/**
	 * @return PLIB_Progress_Manager the progress-manager
	 */
	public function get_progress_manager()
	{
		return $this->_pm;
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
		$msg = $this->_success_msg;
		$msg .= '<p class="bs_block_pad"><a href="'.$this->_back_url.'">';
		$msg .= $this->locale->lang('back').'</a></p>';
		$this->msgs->add_message($msg);
		
		$this->_populate_template();
	}
	
	/**
	 * Adds the variables to the template
	 */
	private function _populate_template()
	{
		$this->tpl->set_template('inc_progress.htm');
		$this->tpl->add_variables(array(
			'not_finished' => !$this->_pm->is_finished(),
			'title' => $this->_title,
			'img_percent' => round($this->_pm->get_percentage(),0),
			'percent' => round($this->_pm->get_percentage(),1),
			'target_url' => $this->_next_url
		));
		$this->tpl->restore_template();
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>