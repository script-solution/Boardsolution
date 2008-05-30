<?php
/**
 * Contains the error-log-task
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The task which deletes timed out error-logs
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_error_log extends PLIB_Tasks_Base
{
	public function run()
	{
		if($this->cfg['error_log_days'] > 0)
			BS_DAO::get_logerrors()->delete_timedout(3600 * 24 * $this->cfg['error_log_days']);
	}
}
?>