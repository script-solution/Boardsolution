<?php
/**
 * Contains the change-pw- and change-email-task
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The task which deletes "dead" change-pw- and change-email-entries
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_change_email_pw extends PLIB_Tasks_Base
{
	public function run()
	{
		// delete pw-changes
		BS_DAO::get_changepw()->delete_timedout(BS_DEAD_REG_DELETE_INTERVAL);
		
		// delete email-changes
		BS_DAO::get_changeemail()->delete_timedout(BS_DEAD_REG_DELETE_INTERVAL);
	}
}
?>