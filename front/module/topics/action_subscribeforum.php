<?php
/**
 * Contains the subscribe-forum-action
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
 * The subscribe-forum-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_topics_subscribeforum extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();
		// has the user the permission to subscribe the forum?
		if(!$user->is_loggedin() || $cfg['enable_email_notification'] == 0 ||
			 !$auth->has_global_permission('subscribe_forums'))
			return 'You are a guest, subscriptions are disabled or you can\'t subscribe to forums';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// is the parameter valid?
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		if($fid == null)
			return 'The forum-id "'.$fid.'" is invalid';

		// check if the forum exists
		$data = $forums->get_node_data($fid);
		if($data === null)
			return 'A forum with id "'.$fid.'" doesn\'t exist';

		// forum not accessable or a category?
		$denied_forums = BS_ForumUtils::get_denied_forums(true);
		if(in_array($fid,$denied_forums))
			return 'The forum is denied for you or a category';

		// has the user already subscribed this forum?
		if(BS_DAO::get_subscr()->has_subscribed_forum($user->get_user_id(),$fid))
			return 'already_subscribed_forum';

		// check if the user is allowed to subscribe this topic
		if($cfg['max_forum_subscriptions'] > 0)
		{
			$subscriptions = BS_DAO::get_subscr()->get_subscr_forums_count($user->get_user_id());
			if($subscriptions >= $cfg['max_forum_subscriptions'])
				return sprintf($locale->lang('error_max_forum_subscriptions'),
											 $cfg['max_forum_subscriptions']);
		}

		BS_DAO::get_subscr()->subscribe_forum($fid,$user->get_user_id());
		
		$this->set_action_performed(true);
		$this->add_link($locale->lang('back'),BS_URL::get_topics_url($fid));
		
		$url = BS_URL::get_sub_url('userprofile','forums');
		$this->add_link($locale->lang('to_profile_subscr'),$url);
		$this->set_success_msg(sprintf($locale->lang('subscription_desc_forum'),$data->get_name()));

		return '';
	}
}
?>