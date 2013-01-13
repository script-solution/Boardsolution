<?php
/**
 * Contains the openclose_topics-action
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
 * The openclose_topics-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_openclose_topics_default extends BS_Front_Action_Base
{
	public function perform_action($mode = 'open')
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// check parameter
		$id_str = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		if($fid == null)
			return 'The forum-id "'.$fid.'" is invalid';

		// does the forum exist?
		if(!$forums->node_exists($fid))
			return 'The forum with id "'.$fid.'" doesn\'t exist';

		// save the topics which have been opened / closed
		$topic_names = array();
		$topic_ids = array();
		$post_reason = $input->get_var('post_reason','post',FWS_Input::INT_BOOL);

		// grab the topics from database
		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// skip this topic, if the user has no permission to open / close it
			if(!$auth->has_current_forum_perm(BS_MODE_OPENCLOSE_TOPICS,$data['post_user']))
				continue;

			// skip this topic if it is a shadow-topic
			if($data['moved_tid'] > 0)
				continue;

			// nothing to do?
			if(($data['thread_closed'] == 1 && $mode == 'close') ||
					($data['thread_closed'] == 0 && $mode == 'open'))
				continue;

			// is the topic locked for the current user?
			if(BS_TopicUtils::is_locked($data['locked'],BS_LOCK_TOPIC_OPENCLOSE))
				continue;

			// post the reason, if required
			if($post_reason == 1)
			{
				// create post
				$post = BS_Front_Action_Plain_Post::get_default($fid,$data['id'],false);
				$res = $post->check_data();
				// any error?
				if($res != '')
					return $res;
				$post->perform_action();
			}

			$topic_names[] = $data['name'];
			$topic_ids[] = $data['id'];
		}

		if(count($topic_ids) > 0)
		{
			$new_status = ($mode == 'open') ? 0 : 1;
			BS_DAO::get_topics()->update_by_ids($topic_ids,array('thread_closed' => $new_status));

			$msg_type = $mode== 'open' ? BS_ACTION_OPEN_TOPICS : BS_ACTION_CLOSE_TOPICS;
			$message = $locale->lang('success_'.$msg_type);
			$this->set_success_msg(sprintf($message,implode('", "',$topic_names)));
		}
		else
			$this->set_success_msg($locale->lang('error_no_topics_opened_closed'));

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back_to_forum'),BS_URL::get_topics_url($fid));

		return '';
	}
}
?>