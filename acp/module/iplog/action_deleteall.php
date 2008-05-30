<?php
/**
 * Contains the delete-all-iplogs-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-all-iplogs-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_iplog_deleteall extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		BS_DAO::get_logips()->clear();
		
		$this->set_success_msg($this->locale->lang('deleted_logs'));
		$this->set_action_performed(true);

		return '';
	}
}
?>