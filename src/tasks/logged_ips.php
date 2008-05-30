<?php
/**
 * Contains the blocked-ip-task
 * 
 * @version			$Id: logged_ips.php 749 2008-05-24 15:33:31Z nasmussen $
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
		BS_DAO::get_logips()->delete_timedout($this->cfg['ip_log_days'] * 86400);
	}
}
?>