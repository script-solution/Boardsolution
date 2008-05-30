<?php
/**
 * Contains the add-language-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add-language-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_languages_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		BS_DAO::get_langs()->create();
		$this->cache->refresh('languages');

		$this->set_success_msg($this->locale->lang('lang_added_notice'));
		$this->set_action_performed(true);

		return '';
	}
}
?>