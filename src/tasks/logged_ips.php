<?php
/**
 * Contains the blocked-ip-task
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The task which deletes timed out ips
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_logged_ips extends PLIB_Tasks_Base
{
	public function run()
	{
		$cfg = PLIB_Props::get()->cfg();

		BS_DAO::get_logips()->delete_timedout($cfg['ip_log_days'] * 86400);
	}
}
?>