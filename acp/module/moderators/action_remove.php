<?php
/**
 * Contains the remove-moderators-action
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
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$fid = $input->get_var('f','get',FWS_Input::ID);
		$uid = $input->get_var('uid','get',FWS_Input::ID);
		if($fid == null || $uid == null)
			return 'GET-parameters "fid" and/or "uid" are invalid';
		
		BS_DAO::get_mods()->delete_user_from_forum($uid,$fid);
		$cache->refresh('moderators');
		
		$this->set_success_msg($locale->lang('remove_moderators_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>