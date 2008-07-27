<?php
/**
 * Contains the edit-bot-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-bot-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_bots_edit extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';
		
		$id = $input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
			return 'The id "'.$id.'" is invalid';
		
		if(!$cache->get_cache('bots')->key_exists($id))
			return 'A bot with id="'.$id.'" does not exist';
		
		$values = BS_ACP_Module_bots::check_values();
		if(!is_array($values))
			return $values;
		
		BS_DAO::get_bots()->update($id,$values);
		$cache->refresh('bots');
		
		$this->set_success_msg($locale->lang('bot_edit_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>