<?php
/**
 * Contains the add-bot-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add-bot-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_bots_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';
		
		$values = BS_ACP_Module_bots::check_values();
		if(!is_array($values))
			return $values;
		
		BS_DAO::get_bots()->create($values);
		$cache->refresh('bots');
		
		$this->set_success_msg($locale->lang('bot_add_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>