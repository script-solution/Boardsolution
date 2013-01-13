<?php
/**
 * Contains the edituser-moderators-action
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$user = $input->get_var('user','post');
		if(!is_array($user) || count($user) == 0 || !FWS_Array_Utils::is_integer($user))
			return 'Got an invalid user-array from POST';
		
		$forums = $input->get_var('forums','post');
		if(!is_array($forums))
			$forums = array();

		// check array
		foreach($forums as $uid => $fids)
		{
			if(!FWS_Helper::is_integer($uid) || !in_array($uid,$user))
				return 'Invalid user-id "'.$uid.'"';
			if(!FWS_Array_Utils::is_integer($fids))
				return 'Invalid forum-ids for user-id "'.$uid.'"';
		}
		
		// delete current forums for the user
		BS_DAO::get_mods()->delete_by_users($user);
		
		// insert new forums
		foreach($forums as $uid => $fids)
			BS_DAO::get_mods()->create_multiple($fids,$uid);
		
		// refresh cache
		$cache->refresh('moderators');
		
		$this->set_success_msg($locale->lang('mod_forums_saved'));
		$this->set_action_performed(true);

		return '';
	}
}
?>