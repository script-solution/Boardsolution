<?php
/**
 * Contains the logout-action
 * 
 * @package			Boardsolution
 * @subpackage	front.src.action
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
 * The logout-action
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_logout extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();

		$username = $user->get_user_name();

		// don't report an error here because this may happen if the session-id doesn't exist anymore
		// and therefore the user is already logged out
		if($user->is_loggedin())
		{
			// check if the session-id is valid
			if(!$functions->has_valid_get_sid())
				return 'Invalid session-id';
	
			$user->logout();
			
			// delete all unread topics for guest and ensure that nothing will be added
			$gunread = new BS_UnreadStorage_Guest();
			$gunread->remove_all();
			$gunread->set_last_update(time());
		}
		
		#$this->set_success_msg(sprintf($locale->lang('success_'.BS_ACTION_LOGOUT),$username));
		#$this->set_redirect(true,BS_URL::get_start_url());
		#$this->add_link($locale->lang('forumindex'),BS_URL::get_start_url());
		#$this->set_action_performed(true);
		
		return '';
	}
}
?>