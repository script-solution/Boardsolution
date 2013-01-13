<?php
/**
 * Contains the vote-poll-action
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
 * The vote-poll-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_posts_votepoll extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		// parameter valid?
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		if($fid == null || $tid == null)
			return 'The forum- or topic-id is invalid';

		$topic_data = BS_DAO::get_polls()->get_data_by_topic_id($tid);
		
		// does the topic exist?
		if($topic_data['type'] == '')
			return 'A topic with id "'.$tid.'" doesn\'t exist';

		// is has to be a not closed poll
		if($topic_data['thread_closed'] == 1 || $topic_data['type'] <= 0)
			return 'The topic is closed or no poll';

		// guests are not allowed to vote
		if(!$user->is_loggedin())
			return 'You are a guest';

		if(BS_UserUtils::user_voted_for_poll($topic_data['type']))
			return 'poll_user_voted';

		if($topic_data['multichoice'] == 0)
		{
			$choice = $input->get_var('vote_option','post',FWS_Input::ID);
			if($choice == null)
				return 'no_radiobutton_clicked';

			BS_DAO::get_polls()->vote($choice);
		}
		else
		{
			$choice = $input->get_var('vote_option','post');
			if($choice == null || count($choice) == 0 || !FWS_Array_Utils::is_integer($choice))
				return 'no_checkbox_clicked';

			foreach($choice as $value)
				BS_DAO::get_polls()->vote($value);
		}

		BS_DAO::get_pollvotes()->create($topic_data['type'],$user->get_user_id());

		$this->set_action_performed(true);
		$this->add_link($locale->lang('go_to_topic'),BS_URL::get_posts_url($fid,$tid));
	
		return '';
	}
}
?>