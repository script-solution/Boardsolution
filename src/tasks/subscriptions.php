<?php
/**
 * Contains the subscriptions-task
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The task which deletes subscriptions of topics which are inactive since a specified
 * period of time
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_subscriptions extends PLIB_Tasks_Base
{
	public function run()
	{
		$sub_ids = BS_DAO::get_subscr()->get_timedout_subscr_ids(BS_SUBSCRIPTION_TIMEOUT);
		if(count($sub_ids) > 0)
			BS_DAO::get_subscr()->delete_by_ids($sub_ids);
	}
}
?>