<?php
/**
 * Contains the edituser-moderators-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edituser-moderators-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_moderators_edituser extends BS_ACP_Action_Base
{
	function perform_action()
	{
		$user = $this->input->get_var('user','post');
		if(!is_array($user) || count($user) == 0 || !PLIB_Array_Utils::is_integer($user))
			return 'Got an invalid user-array from POST';
		
		$forums = $this->input->get_var('forums','post');
		if(!is_array($forums))
			$forums = array();

		// check array
		foreach($forums as $uid => $fids)
		{
			if(!PLIB_Helper::is_integer($uid) || !in_array($uid,$user))
				return 'Invalid user-id "'.$uid.'"';
			if(!PLIB_Array_Utils::is_integer($fids))
				return 'Invalid forum-ids for user-id "'.$uid.'"';
		}
		
		// delete current forums for the user
		BS_DAO::get_mods()->delete_by_users($user);
		
		// insert new forums
		foreach($forums as $uid => $fids)
			BS_DAO::get_mods()->create_multiple($fids,$uid);
		
		// refresh cache
		$this->cache->refresh('moderators');
		
		$this->set_success_msg($this->locale->lang('mod_forums_saved'));
		$this->set_action_performed(true);

		return '';
	}
}
?>