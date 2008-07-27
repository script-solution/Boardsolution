<?php
/**
 * Contains the add-ban-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add-ban-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_banlist_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();

		BS_DAO::get_bans()->create();
		$cache->refresh('banlist');
		
		$this->set_success_msg($locale->lang('bansystem_add_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>