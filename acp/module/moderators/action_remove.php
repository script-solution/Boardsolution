<?php
/**
 * Contains the remove-moderators-action
 *
 * @version			$Id: action_remove.php 733 2008-05-23 06:40:04Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The remove-moderators-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_moderators_remove extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$fid = $this->input->get_var('f','get',PLIB_Input::ID);
		$uid = $this->input->get_var('uid','get',PLIB_Input::ID);
		if($fid == null || $uid == null)
			return 'GET-parameters "fid" and/or "uid" are invalid';
		
		BS_DAO::get_mods()->delete_user_from_forum($uid,$fid);
		$this->cache->refresh('moderators');
		
		$this->set_success_msg($this->locale->lang('remove_moderators_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>