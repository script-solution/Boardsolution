<?php
/**
 * Contains the move-topics-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The move-topics-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_move_topics_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// parameters valid?
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$id_str = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';

		$target_fid = $input->get_var('target_forum','post',FWS_Input::ID);

		if($fid == null || $target_fid == null)
			return 'The forum-id "'.$fid.'" or "'.$target_fid.'" is invalid';

		if(!$forums->node_exists($fid) || !$forums->node_exists($target_fid))
			return 'The forum-id "'.$fid.'" or "'.$target_fid.'" doesn\'t exist';
		
		// check if the target-forum is equal to the source-forum
		if($fid == $target_fid)
			return 'movetootherrubrik';

		// has the user the permission to move the topics?
		if(!$user->is_loggedin() || !$auth->has_current_forum_perm(BS_MODE_MOVE_TOPICS))
			return 'You are a guest or have no permission to move topics';

		$post_reason = $input->get_var('post_reason','post',FWS_Input::INT_BOOL);
		$leave_link = $input->get_var('leave_link','post',FWS_Input::INT_BOOL);

		$forum_data = $forums->get_node_data($fid);
		$target_forum_data = $forums->get_node_data($target_fid);
		if($target_forum_data->get_forum_type() == 'contains_cats')
			return 'You can\'t move the topic to a forum that contains forums';

		$total_posts = 0;
		$change_source_last_post = false;
		$max_target_last_post_id = 0;

		$moved_topics = array();
		$moved_topic_ids = array();
		foreach(BS_DAO::get_topics()->get_by_ids($ids,$fid) as $data)
		{
			// don't move shadow-topics
			if($data['moved_tid'] > 0)
				continue;

			// do we have to change the lastpost of the source-forum?
			// do it here because we need the old lastpost_id for this
			if($forum_data->get_lastpost_id() == $data['lastpost_id'])
				$change_source_last_post = true;

			if($post_reason == 1)
			{
				// create post
				$post = BS_Front_Action_Plain_Post::get_default($target_fid,$data['id'],false);
				$res = $post->check_data();
				// any error?
				if($res != '')
					return $res;
				$post->perform_action();

				// we have made a post, so the lastpost_id has to be changed
				$data['lastpost_id'] = $post->get_post_id();
			}

			if($leave_link == 1)
			{
				$fields = array(
					'rubrikid' => $fid,
					'type' => $data['type'],
					'name' => addslashes($data['name']),
					'post_time' => $data['post_time'],
					'symbol' => $data['symbol'],
					'lastpost_time' => $data['lastpost_time'],
					'important' => $data['important'],
					'moved' => 1,
					'moved_tid' => $data['id'],
					'moved_rid' => $target_fid
				);
				BS_DAO::get_topics()->create($fields);
			}

			// + 1 because we store the number of replies in the db, not the number of posts
			$total_posts += $data['posts'] + 1;

			// determine the new lastpost_id for the target-forum
			if($data['lastpost_id'] > $target_forum_data->get_lastpost_id() &&
					$data['lastpost_id'] > $max_target_last_post_id)
				$max_target_last_post_id = $data['lastpost_id'];

			$moved_topics[] = $data['name'];
			$moved_topic_ids[] = $data['id'];
		}

		$num = count($moved_topic_ids);
		if($num > 0)
		{
			// update target forum
			$fields = array(
				'threads' => array('threads + '.$num),
				'posts' => array('posts + '.$total_posts)
			);
			
			if($max_target_last_post_id > 0)
				$fields['lastpost_id'] = $max_target_last_post_id;
			
			// if we're moving topics to an intern forum, we have to remove unread-posts of
			// the users that don't have access to it
			if($target_forum_data->get_forum_is_intern())
			{
				$delposts = array();
				$unread_posts = BS_DAO::get_unread()->get_posts_for_topic_move($moved_topic_ids);
				foreach($unread_posts as $data)
				{
					// check if the user has access
					$groups = FWS_Array_Utils::advanced_explode(',',$data['user_group']);
					if(!$functions->has_access_to_intern_forum($data['user_id'],$groups,$target_fid))
					{
						if(!isset($delposts[$data['user_id']]))
							$delposts[$data['user_id']] = array();
						$delposts[$data['user_id']][] = $data['post_id'];
					}
				}
				
				// delete them
				foreach($delposts as $uid => $postids)
					BS_DAO::get_unread()->delete_posts_of_user($uid,$postids);
			}

			BS_DAO::get_forums()->update_by_id($target_fid,$fields);

			// update the moved topics
			BS_DAO::get_topics()->update_by_ids(
				$moved_topic_ids,array('moved' => 1,'rubrikid' => $target_fid)
			);

			BS_DAO::get_posts()->update_by_topics(
				$moved_topic_ids,array('rubrikid' => $target_fid)
			);

			// update source-forum
			$fields = array(
				'posts' => array('posts - '.$total_posts)
			);
			
			if($change_source_last_post)
			{
				$flastpost = BS_DAO::get_posts()->get_lastpost_id_in_forum($fid);
				$fields['lastpost_id'] = $flastpost;
			}
			
			if($leave_link == 0)
				$fields['threads'] = array('threads - '.$num);

			BS_DAO::get_forums()->update_by_id($fid,$fields);

			// correct shadow-threads
			BS_DAO::get_topics()->update_shadows_by_ids(
				$moved_topic_ids,array('moved_rid' => $target_fid)
			);
			
			// do we have to change the user-experience?
			if($forum_data->get_increase_experience() != $target_forum_data->get_increase_experience())
			{
				$user_exp = array();
				$start_posts = array();
				foreach(BS_DAO::get_posts()->get_posts_by_topics($moved_topic_ids) as $data)
				{
					// determine the first post in every topic
					if(!isset($start_posts[$data['threadid']]) ||
							$data['post_time'] < $start_posts[$data['threadid']][1])
						$start_posts[$data['threadid']] = array($data['post_user'],$data['post_time']);

					if(!isset($user_exp[$data['post_user']]))
						$user_exp[$data['post_user']] = 0;

					$user_exp[$data['post_user']] += BS_EXPERIENCE_FOR_POST;
				}

				// increase the experience by BS_EXPERIENCE_FOR_TOPIC for the topic-creation
				foreach($start_posts as $content)
					$user_exp[$content[0]] += BS_EXPERIENCE_FOR_TOPIC;

				// finally change the experience
				if(!$forum_data->get_increase_experience() && $target_forum_data->get_increase_experience())
				{
					foreach($user_exp as $user_id => $exp)
					{
						// if we have added a reason in the target-forum we HAVE got the experience for
						// this post but we've found the post in the query above..so we have to decrease
						// the experience to add by the number of moved topics
						if($post_reason == 1)
							$exp -= BS_EXPERIENCE_FOR_POST * $num;

						BS_DAO::get_profile()->update_user_by_id(
							array('exppoints' => array('exppoints + '.$exp)),$user_id
						);
					}
				}
				else
				{
					foreach($user_exp as $user_id => $exp)
					{
						// if we have added a reason in the target-forum we HAVE NOT got the experience
						// for this post but we've found the post in the query above..so we have to decrease
						// the experience to substract by the number of moved topics
						if($post_reason == 1)
							$exp -= BS_EXPERIENCE_FOR_POST * $num;

						BS_DAO::get_profile()->update_user_by_id(
							array('exppoints' => array('exppoints - '.$exp)),$user_id
						);
					}
				}
			}

			$this->set_success_msg(sprintf(
				$locale->lang('success_'.BS_ACTION_MOVE_TOPICS),
				implode('", "',$moved_topics)
			));
		}
		else
			$this->set_success_msg($locale->lang('error_no_topics_moved'));

		$this->set_action_performed(true);
		$this->add_link($locale->lang('back_to_forum'),BS_URL::get_topics_url($fid));
		if($num == 1)
		{
			$murl = BS_URL::get_mod_url('posts');
			$murl->set(BS_URL_FID,$target_fid);
			$murl->set(BS_URL_TID,$moved_topic_ids[0]);
			$murl->set_sef(true);
			$this->add_link($locale->lang('to_moved_thread'),$murl);
		}

		return '';
	}
}
?>