<?php
/**
 * Contains the delete-post-action
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
 * The delete-post-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_delete_post_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$functions = FWS_Props::get()->functions();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		if(!$input->isset_var('submit','post'))
			return '';

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);

		// check ids of the posts to delete
		$ids = $input->get_var('selected_posts','post');
		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			return 'split_no_posts_selected';

		// check other parameters
		if($fid == null || $tid == null)
			return 'Invalid forum-id or topic-id';

		// does the forum exist?
		$forum_data = $forums->get_node_data($fid);
		if($forum_data === null)
			return 'Forum with id "'.$fid.'" not found';

		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
			return 'You are no admin and the forum is closed';

		// does the topic exist?
		$topic_data = BS_Front_TopicFactory::get_current_topic();
		if($topic_data == null)
			return 'The topic has not been found';

		// topic closed?
		if($topic_data['thread_closed'] == 1 && !$user->is_admin())
			return 'You are no admin and the topic is closed';

		// collect deleteable posts
		$first_post = BS_DAO::get_posts()->get_first_postid_in_topic($fid,$tid);

		$selected_posts = array();
		$order = BS_PostingUtils::get_posts_order();
		foreach(BS_DAO::get_posts()->get_posts_from_topic($ids,$fid,$tid,'p.id',$order) as $data)
		{
			// ensure that nobody can perform an action with the first post of a topic
			if($data['id'] == $first_post)
				continue;

			// check if the user has permission to perform the action
			if(!$auth->has_current_forum_perm(BS_MODE_DELETE_POSTS,$data['post_user']))
				continue;

			$selected_posts[] = $data;
		}

		// check if the user has chosen valid posts
		$total_posts = count($selected_posts);
		if($total_posts == 0)
			return 'split_no_posts_selected';

		$post_ids = array();
		$max_post_time = 0;
		$user_posts = array();
		$refresh_topic_last_post = false;
		$refresh_forum_last_post = false;
		foreach($selected_posts as $data)
		{
			// do we have to refresh the last post in the topic / forum?
			if($topic_data['lastpost_id'] == $data['id'])
			{
				$refresh_topic_last_post = true;
				if($forum_data->get_lastpost_id() == $data['id'])
					$refresh_forum_last_post = true;
			}

			// save the number of posts to decrease for each user
			if($data['post_user'] > 0)
			{
				if(isset($user_posts[$data['post_user']]))
					$user_posts[$data['post_user']]++;
				else
					$user_posts[$data['post_user']] = 1;
			}

			// save the maximum post-time to refresh the lastpost in the stats-table if required
			if($data['post_time'] > $max_post_time)
				$max_post_time = $data['post_time'];

			$post_ids[] = $data['id'];
		}

		$post_id_str = implode(',',$post_ids);

		// generate the additional SQL-code if we have to refresh the last post in the topic and/or forum
		$forum_fields = array(
			'posts' => array('posts - '.$total_posts)
		);
		$topic_fields = array(
			'posts' => array('posts - '.$total_posts)
		);
		
		if($refresh_topic_last_post)
		{
			$topic_lastpost = BS_DAO::get_posts()->get_lastpost_data_in_topic($tid,$post_ids);
			$topic_fields['lastpost_id'] = $topic_lastpost['id'];
			$topic_fields['lastpost_time'] = $topic_lastpost['post_time'];
			$topic_fields['lastpost_user'] = $topic_lastpost['post_user'];
			$topic_fields['lastpost_an_user'] = $topic_lastpost['post_an_user'];
			
			if($refresh_forum_last_post)
			{
				$flastpost = BS_DAO::get_posts()->get_lastpost_id_in_forum($fid,$post_ids);
				$forum_fields['lastpost_id'] = $flastpost;
			}
		}
		
		BS_DAO::get_topics()->update($tid,$topic_fields);
		BS_DAO::get_forums()->update_by_id($fid,$forum_fields);
		
		$forum_data = $forums->get_node_data($fid);

		// decrease the posts of the user
		foreach($user_posts as $user_id => $number)
		{
			$fields = array('posts' => array('posts - '.$number));
			if($forum_data->get_increase_experience())
				$fields['exppoints'] = array('exppoints - '.($number * BS_EXPERIENCE_FOR_POST));

			BS_DAO::get_profile()->update_user_by_id($fields,$user_id);
		}

		if($post_id_str != '')
		{
			// remove them from the unread
			BS_UnreadUtils::remove_posts($post_ids,$tid);
			
			// remove attachments
			foreach(BS_DAO::get_attachments()->get_by_postids($post_ids) as $adata)
				$functions->delete_attachment($adata['attachment_path']);

			BS_DAO::get_attachments()->delete_by_postids($post_ids);
			
			// remove the posts
			BS_DAO::get_posts()->delete_by_ids($post_ids);
		}

		BS_Front_Action_Helper::adjust_last_post_time($max_post_time);
		
		// build URL
		$murl = BS_URL::get_mod_url('posts');
		$murl->set(BS_URL_FID,$fid);
		$murl->set(BS_URL_TID,$tid);
		$lastpost = BS_DAO::get_posts()->get_lastpost_data_in_topic($tid);
		if(BS_PostingUtils::get_posts_order() == 'ASC')
		{
			$post_num = BS_DAO::get_posts()->get_count_in_topic($tid);
			if($post_num > $cfg['posts_per_page'])
			{
				$pagination = new BS_Pagination($cfg['posts_per_page'],$post_num);
				$murl->set(BS_URL_SITE,$pagination->get_page_count());
			}
		}
		$murl->set_anchor('b_'.$lastpost['id']);
		
		$this->add_link($locale->lang('go_to_topic'),$murl);
		$this->set_action_performed(true);

		return '';
	}
}
?>