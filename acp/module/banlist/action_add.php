<?php
/**
 * Contains the add-ban-action
 *
 * @version			$Id: action_add.php 719 2008-05-21 14:28:56Z nasmussen $
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
		BS_DAO::get_bans()->create();
		$this->cache->refresh('banlist');
		
		$this->set_success_msg($this->locale->lang('bansystem_add_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>