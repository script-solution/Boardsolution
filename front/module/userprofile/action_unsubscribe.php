<?php
/**
 * Contains the unsubscribe-action
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
 * The unsubscribe-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_unsubscribe extends BS_Front_Action_Base
{
	public function perform_action($type = 'forums')
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		// has the user the permission to unsubscribe forums?
		if(!$user->is_loggedin() || $cfg['enable_email_notification'] == 0 ||
			 ($type == 'forums' && !$auth->has_global_permission('subscribe_forums')))
			return 'You are a guest, subscriptions are disabled or you have no permission
				to subscribe to forums';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check the parameter
		$id_str = $input->get_var(BS_URL_DEL,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-array via POST';

		BS_DAO::get_subscr()->delete_by_ids_from_user($ids,$user->get_user_id());

		$this->set_action_performed(true);
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		$url = BS_URL::get_sub_url('userprofile',$type);
		$url->set(BS_URL_SITE,$site);
		$this->add_link($locale->lang('back'),$url);

		return '';
	}
}
?>