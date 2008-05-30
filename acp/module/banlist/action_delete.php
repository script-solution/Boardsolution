<?php
/**
 * Contains the delete-bans-action
 *
 * @version			$Id: action_delete.php 719 2008-05-21 14:28:56Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-bans-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_banlist_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		$rows = BS_DAO::get_bans()->delete_by_ids($ids);
		if($rows > 0)
			$this->cache->refresh('banlist');
		
		$this->set_success_msg($this->locale->lang('bansystem_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>