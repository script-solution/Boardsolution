<?php
/**
 * Contains the pm-ban-user-action
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
 * The pm-ban-user-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_pmbanuser extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();

		// check if we are allowed to ban a user
		if(!$user->is_loggedin() || $cfg['enable_pms'] == 0 ||
				$user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled or you\'ve disabled PMs';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		// if the id does not exist, check if a username does
		if($id == null)
		{
			$username = $input->get_var('user_name','post',FWS_Input::STRING);
			if($username == null)
				return 'banlist_user_not_found';

			// check if a user with this name exists
			$data = BS_DAO::get_user()->get_user_by_name($username);
			if($data === false)
				return 'banlist_user_not_found';

			$id = $data['id'];
		}
		// otherwise check if the id exists
		else if(!BS_DAO::get_user()->id_exists($id))
			return 'banlist_user_not_found';

		// we do not want to ban ourself ;)
		if($id == $user->get_user_id())
			return 'banlist_self';

		// check if the user is already on our banlist
		if(BS_DAO::get_userbans()->has_baned($user->get_user_id(),$id))
			return 'banlist_user_exists';

		// everything ok, so ban the user
		BS_DAO::get_userbans()->create($user->get_user_id(),$id);

		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),BS_URL::get_sub_url('userprofile','pmbanlist')
		);

		return '';
	}
}
?>