<?php
/**
 * Contains the add-event-action
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
 * The add-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_calendar_addevent extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';
		
		// has the user permission to start an event?
		if($cfg['enable_calendar_events'] == 0 ||
			!$auth->has_global_permission('add_cal_event'))
			return 'Calendar-events disabled or no permission to add events';

		$event = BS_Front_Action_Plain_Event::get_default();
		$res = $event->check_data();
		if($res != '')
			return $res;
		
		$event->perform_action();

		$this->add_link($locale->lang('back'),BS_URL::get_mod_url('calendar'));
		$this->set_action_performed(true);

		return '';
	}
}
?>