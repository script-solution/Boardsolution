<?php
/**
 * Contains the delete-event-action
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
 * The delete-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_calendar_deleteevent extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		// check parameter
		$id = $input->get_var(BS_URL_DEL,'get',FWS_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check if the event exists
		$event_data = BS_DAO::get_events()->get_by_id($id);
		if($event_data === false || $event_data['tid'] != 0)
			return 'Event with id "'.$id.'" not found or no calendar-event';

		// check permission
		if(!$user->is_admin())
		{
			if($event_data['user_id'] != $user->get_user_id() ||
				!$auth->has_global_permission('delete_cal_event'))
				return 'Not your own event or no permission to delete events';
		}

		// delete the event
		BS_DAO::get_events()->delete_by_ids(array($id));

		$this->add_link($locale->lang('back'),BS_URL::get_mod_url('calendar'));
		$this->set_action_performed(true);

		return '';
	}
}
?>