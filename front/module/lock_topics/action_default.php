<?php
/**
 * Contains the edit-event-action
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
 * The edit-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_lock_topics_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$id_str = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Invalid id-string got via GET';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		if($fid == null)
			return 'The forum-id "'.$fid.'" is invalid';

		$topic_ids = array();

		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// skip this topic if the user is not allowed to delete it
			if(!$auth->has_current_forum_perm(BS_MODE_LOCK_TOPICS))
				continue;

			// check if this is a shadow topic
			if($data['moved_tid'] != 0)
				continue;

			$topic_ids[] = $data['id'];
		}

		// no valid topics?
		if(count($topic_ids) == 0)
			return 'no_topics_chosen';

		// grab vars
		$edit_topic = $input->get_var('edit_topic','post',FWS_Input::INT_BOOL);
		$openclose_topic = $input->get_var('openclose_topic','post',FWS_Input::INT_BOOL);
		$posts_topic = $input->get_var('posts_topic','post',FWS_Input::INT_BOOL);

		// build locked-value
		$locked = 0;
		if($edit_topic == 1)
			$locked |= BS_LOCK_TOPIC_EDIT;
		if($openclose_topic == 1)
			$locked |= BS_LOCK_TOPIC_OPENCLOSE;
		if($posts_topic == 1)
			$locked |= BS_LOCK_TOPIC_POSTS;

		// set new locked-status
		BS_DAO::get_topics()->update_by_ids($topic_ids,array('locked' => $locked));

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back'),BS_URL::get_topics_url($fid));

		return '';
	}
}
?>