<?php
/**
 * Contains the subscribe-all-action
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
 * The subscribe-all-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_subscribeall extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$functions = FWS_Props::get()->functions();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();
		// is the user loggedin?
		if($cfg['enable_email_notification'] == 0 || !$user->is_loggedin())
			return 'Subscriptions are disabled or you are a guest';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// collect all existing forum-subscriptions
		$ex_fids = array();
		foreach(BS_DAO::get_subscr()->get_subscr_forums_of_user($user->get_user_id()) as $data)
			$ex_fids[$data['forum_id']] = true;

		$denied_forums = BS_ForumUtils::get_denied_forums(true);

		// get all missing forum-ids
		$fids = array();
		foreach($forums->get_all_nodes() as $fnode)
		{
			$fdata = $fnode->get_data();
			// denied forum or category?
			if(in_array($fdata->get_id(),$denied_forums))
				continue;

			if(!isset($ex_fids[$fdata->get_id()]))
				$fids[] = $fdata->get_id();
		}

		// do we have a subscription-limit?
		if($cfg['max_forum_subscriptions'] > 0)
		{
			if(count($fids) + count($ex_fids) > $cfg['max_forum_subscriptions'])
			{
				return sprintf(
					$locale->lang('error_subscribe_all_not_possible'),
					$cfg['max_forum_subscriptions']
				);
			}
		}

		// add all missing forum-ids
		foreach($fids as $fid)
			BS_DAO::get_subscr()->subscribe_forum($fid,$user->get_user_id());
		
		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),BS_URL::get_sub_url('userprofile','forums')
		);

		return '';
	}
}
?>