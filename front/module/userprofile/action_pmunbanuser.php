<?php
/**
 * Contains the pm-unban-user-action
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The pm-unban-user-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_pmunbanuser extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		// check if we are allowed to unban a user
		if(!$user->is_loggedin() || $cfg['enable_pms'] == 0 ||
				$user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled or you\'ve disabled PMs';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check parameter
		$ids = $input->get_var(BS_URL_DEL,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($ids)))
			return 'Invalid id-sstring got via GET';

		// delete the user from our banlist
		BS_DAO::get_userbans()->delete_bans_of_user($user->get_user_id(),$ids);

		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),BS_URL::get_sub_url('userprofile','pmbanlist')
		);
		
		return '';
	}
}
?>