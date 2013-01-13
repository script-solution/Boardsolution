<?php
/**
 * Contains the subscribe-topic-action
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
 * The subscribe-topic-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_posts_subscribetopic extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		if(!$user->is_loggedin())
			return 'nichteingeloggt';

		if($cfg['enable_email_notification'] == 0)
			return 'Subscriptions are disabled';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// are the parameters valid?
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		if($fid == null || $tid == null)
			return 'The forum-id or topic-id is invalid';

		// forum not accessable or a category?
		$denied_forums = BS_ForumUtils::get_denied_forums(true);
		if(in_array($fid,$denied_forums))
			return 'The forum is denied for you or a category';

		$sub = BS_Front_Action_Plain_SubscribeTopic::get_default($tid);
		$res = $sub->check_data();
		if($res != '')
			return $res;
		
		$sub->perform_action();

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back'),BS_URL::get_posts_url($fid,$tid));
		
		$murl = BS_URL::get_sub_url('userprofile','topics');
		$this->add_link($locale->lang('to_profile_subscr'),$murl);
		$this->set_success_msg(
			sprintf($locale->lang('subscription_desc_topic'),$sub->get_topic_name())
		);

		return '';
	}
}
?>