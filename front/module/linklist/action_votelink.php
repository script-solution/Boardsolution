<?php
/**
 * Contains the vote-link-action
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
 * The vote-link-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_linklist_votelink extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$vote = $input->get_var('link_rating_'.$id,'post',FWS_Input::INTEGER);

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check parameters
		if($id == null || $vote == null || !$user->is_loggedin())
			return 'The id or your rating is invalid or you\'re a guest';

		// has the user already voted?
		if(BS_UserUtils::user_voted_for_link($id))
			return 'already_voted';

		// check if the vote is valid
		if($vote < 1 || $vote > 6)
			return 'invalid_vote_option';

		BS_DAO::get_links()->vote($id,$vote);
		BS_DAO::get_linkvotes()->vote($id,$user->get_user_id());

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back'),BS_URL::get_mod_url('linklist'));

		return '';
	}
}
?>