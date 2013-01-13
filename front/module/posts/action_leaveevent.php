<?php
/**
 * Contains the leave-event-action
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
 * The leave-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_posts_leaveevent extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		// is the user loggedin?
		if($cfg['enable_events'] == 0 || !$user->is_loggedin())
			return 'Events are disabled or you are not loggedin';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'The session-id is invalid';

		// check parameters
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);

		if($fid == null || $tid == null)
			return 'The forum-id or topic-id is invalid';

		// does the topic exist?
		$data = BS_DAO::get_topics()->get_by_id($tid);
		if($data === false)
			return 'A topic with id "'.$tid.'" has not been found';
		
		$event = BS_DAO::get_events()->get_by_topic_id($tid);
		if($event === false || $event['max_announcements'] < 0)
			return 'An event with topic-id "'.$tid.'" and enabled announcements has not been found';
		
		// is the user announced to this event?
		if(!BS_DAO::get_eventann()->is_announced($user->get_user_id(),$event['id']))
			return 'You are not announced to this event';

		$timeout = ($event['timeout'] == 0) ? $event['event_begin'] : $event['timeout'];
		if($data['thread_closed'] == 1 || time() > $timeout)
			return 'topic_closed';

		BS_DAO::get_eventann()->leave($user->get_user_id(),$event['id']);

		$this->set_action_performed(true);
		$this->add_link($locale->lang('go_to_topic'),BS_URL::get_posts_url($fid,$tid));

		return '';
	}
}
?>