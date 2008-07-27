<?php
/**
 * Contains the add-userranks-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add-userranks-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_userranks_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();

		BS_DAO::get_ranks()->create();
		$cache->refresh('user_ranks');
		
		$this->set_success_msg($locale->lang('user_rank_added'));
		$this->set_action_performed(true);

		return '';
	}
}
?>