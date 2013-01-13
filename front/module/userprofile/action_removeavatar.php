<?php
/**
 * Contains the remove-avatar-action
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
 * The remove-avatar-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_removeavatar extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		// has the user the permission?
		if(!$user->is_loggedin() || $cfg['enable_avatars'] == 0)
			return 'You are a guest or avatars are disabled';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// remove the avatar
		BS_DAO::get_profile()->update_user_by_id(array('avatar' => 0),$user->get_user_id());
		$user->set_profile_val('avatar',0);

		$this->set_action_performed(true);
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		$url = BS_URL::get_sub_url('userprofile','avatars');
		$url->set(BS_URL_SITE,$site);
		$this->add_link($locale->lang('back'),$url);

		return '';
	}
}
?>