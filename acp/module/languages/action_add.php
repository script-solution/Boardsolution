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
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		BS_DAO::get_langs()->create();
		$cache->refresh('languages');

		$this->set_success_msg($locale->lang('lang_added_notice'));
		$this->set_action_performed(true);

		return '';
	}
}
?>