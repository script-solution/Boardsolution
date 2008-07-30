<?php
/**
 * Contains the move_posts-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The move_posts-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_manage_posts_default extends BS_Front_Action_Base
{
	public function perform_action($type = 'split')
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		if(!$input->isset_var('submit','post'))
			return '';

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		$split_type = $input->correct_var('split_type','post',FWS_Input::STRING,
			array('selected','following'),'selected');
		$merge_type = $input->correct_var('merge_type','post',FWS_Input::STRING,
			array('selected','following'),'selected');

		// check other parameters
		if($fid == null || $tid == null)
			return 'One of the GET-parameters "fid" and "tid" is invalid';

		// does the forum exist?
		$forum_data = $forums->get_node_data($fid);
		if($forum_data === null)
			return 'The forum with id "'.$fid.'" doesn\'t exist';

		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
			return 'You are no admin and the forum is closed';
		
		// does the topic exist?
		$topic_data = BS_Front_TopicFactory::get_instance()->get_current_topic();
		if($topic_data == null)
			return 'The topic doesn\'t exist';

		// topic closed?
		if($topic_data['thread_closed'] == 1 && !$user->is_admin())
			return 'You are no admin and the thread is closed';

		// permission to split / merge posts?
		if(!$auth->has_current_forum_perm(BS_MODE_SPLIT_POSTS))
			return 'You have no permission to split posts';

		// build where-clause depending on type
		$post_data = array();
		$post_ids = array();
		$ids = $input->get_var('selected_posts','post');
		if(($type == 'split' && $split_type == 'selected') ||
				($type == 'merge' && $merge_type == 'selected'))
		{
			if(FWS_Array_Utils::is_integer($ids) && count($ids) > 0)
				$post_data = BS_DAO::get_posts()->get_posts_by_ids($ids,$fid,$tid);
		}
		else
		{
			$first = is_array($ids) ? current($ids) : null;
			if($first != null)
				$post_data = BS_DAO::get_posts()->get_following_posts($first,$fid,$tid);
		}
		
		foreach($post_data as $data)
			$post_ids[] = $data['id'];
		$total_posts = count($post_ids);

		// has the user selected at least one post?
		if($total_posts == 0)
			return 'split_no_posts_selected';

		// determine the total number of existing posts for this topic
		$ex_post_num = BS_DAO::get_posts()->get_count_in_topic($tid);
		$complete_move = $total_posts == $ex_post_num;

		if($type == 'split')
		{
			// check the target-forum
			$target_fid = $input->get_var('target_forum','post',FWS_Input::ID);
			if($target_fid == null || !$forums->node_exists($target_fid))
				return 'The target-forum "'.$target_fid.'" doesn\'t exist';

			$topic_name = $input->get_var('new_topic_name','post',FWS_Input::STRING);
			if(trim($topic_name) == '')
				return 'missing_topic_name';

			$symbol = $input->get_var('symbol','post',FWS_Input::INTEGER);
			$symbol = ($symbol > BS_NUMBER_OF_TOPIC_ICONS || $symbol < 0) ? 0 : (int)$symbol;
		}
		else
		{
			// determine target-forum and check topic-id
			$topic_id = $input->get_var('topic_id','post',FWS_Input::ID);
			if($topic_id == null)
				return 'missing_topic_id';

			$target_topic_data = BS_DAO::get_topics()->get_by_id($topic_id);
			if($target_topic_data === false)
				return 'invalid_topic_id';

			$target_fid = $target_topic_data['rubrikid'];
		}

		// do we have to refresh the last post of the current topic?
		$refresh_lastpost = false;
		foreach($post_ids as $pid)
		{
			if($pid == $topic_data['lastpost_id'])
			{
				$refresh_lastpost = true;
				break;
			}
		}

		// update the attributes of the source-topic
		if(!$complete_move)
		{
			$topic_fields = array(
				'posts' => array('posts - '.$total_posts)
			);
			
			if($refresh_lastpost)
			{
				$topic_lastpost = BS_DAO::get_posts()->get_lastpost_data_in_topic($tid,$post_ids);
				$topic_fields['lastpost_id'] = $topic_lastpost['id'];
				$topic_fields['lastpost_time'] = $topic_lastpost['post_time'];
				$topic_fields['lastpost_user'] = $topic_lastpost['post_user'];
				$topic_fields['lastpost_an_user'] = $topic_lastpost['post_an_user'];
			}
			
			BS_DAO::get_topics()->update($tid,$topic_fields);
		}

		$first_post = $post_data[0];
		$last_post = $post_data[$total_posts - 1];
		$target_forum_data = $forums->get_node_data($target_fid);

		// if we move the posts to a topic in another forum we have to change the number of posts and
		// potentially the last post
		$target_fid_fields = array();
		
		if($fid != $target_fid)
		{
			$fields = array(
				'posts' => array('posts - '.$total_posts)
			);
			
			// update attributes of the source-forum
			if($refresh_lastpost)
			{
				$flastpost = BS_DAO::get_posts()->get_lastpost_id_in_forum($fid,$post_ids);
				$fields['lastpost_id'] = $flastpost;
			}
			
			if($complete_move)
				$fields['threads'] = array('threads - 1');

			BS_DAO::get_forums()->update_by_id($fid,$fields);

			$target_fid_fields['posts'] = array('posts + '.$total_posts);
			if($target_forum_data->get_lastpost_id() < $last_post['id'])
				$target_fid_fields['lastpost_id'] = $last_post['id'];
		}

		// if we merge the posts to another topic and remove the source-topic, we have to
		// reduce the number of topics for the source-forum
		if($type == 'merge' && $complete_move && $fid == $target_fid)
			$target_fid_fields['threads'] = array('threads - 1');
		// if we move the posts to the same forum and don't remove the source-topic we add one topic
		else if($type == 'split' && $fid == $target_fid && !$complete_move)
			$target_fid_fields['threads'] = array('threads + 1');
		// if we move the posts to another forum, we have to add a topic there
		else if($type == 'split' && $fid != $target_fid)
			$target_fid_fields['threads'] = array('threads + 1');

		// adjust target-forum
		if(count($target_fid_fields) > 0)
			BS_DAO::get_forums()->update_by_id($target_fid,$target_fid_fields);


		$last_post['post_an_user'] = empty($last_post['post_an_user']) ?
			'NULL' : "'".$last_post['post_an_user']."'";

		// create topic
		if($type == 'split')
		{
			$first_post['post_an_user'] = empty($first_post['post_an_user']) ?
				'NULL' : "'".$first_post['post_an_user']."'";
			$first_post['post_an_mail'] = empty($first_post['post_an_mail']) ?
				'NULL' : "'".$first_post['post_an_mail']."'";

			// insert the new topic
			$fields = array(
				'rubrikid' => $target_fid,
				'name' => $topic_name,
				'posts' => $total_posts - 1,
				'post_user' => $first_post['post_user'],
				'post_time' => $first_post['post_time'],
				'post_an_user' => $first_post['post_an_user'],
				'post_an_mail' => $first_post['post_an_mail'],
				'lastpost_user' => $last_post['post_user'],
				'lastpost_time' => $last_post['post_time'],
				'lastpost_an_user' => $last_post['post_an_user'],
				'symbol' => $symbol,
				'type' => 0,
				'comallow' => 1,
				'important' => 0,
				'lastpost_id' => $last_post['id']
			);
			$topic_id = BS_DAO::get_topics()->create($fields);
		}
		else
		{
			$fields = array(
				'posts' => array('posts + '.$total_posts)
			);
			
			// is it the lastpost in the posts we want to add?
			// if so, we apply the last-post attributes
			if($last_post['id'] > $target_topic_data['lastpost_id'])
			{
				$fields['lastpost_id'] = $last_post['id'];
				$fields['lastpost_user'] = $last_post['post_user'];
				$fields['lastpost_an_user'] = $last_post['post_an_user'];
				$fields['lastpost_time'] = $last_post['post_time'];
			}
			// otherwise the last post can be left untouched
			
			// if the first post we move has been created before the target-topic we
			// have to update the creation time of the topic
			if($first_post['post_time'] < $target_topic_data['post_time'])
			{
				$fields['post_time'] = $first_post['post_time'];
				$fields['post_user'] = $first_post['post_user'];
			}
			
			BS_DAO::get_topics()->update($topic_id,$fields);
		}

		// both forums increase the experience?
		if($target_forum_data->get_increase_experience() && $forum_data->get_increase_experience())
		{
			// we have to increase the experience of the user who created the first moved post
			// just do that if we split a topic, that means we create a new one
			if($first_post['post_user'] > 0 && $type == 'split' && !$complete_move)
			{
				$fields = array(
					'exppoints' => array('exppoints + '.BS_EXPERIENCE_FOR_TOPIC)
				);
				BS_DAO::get_profile()->update_user_by_id($fields,$first_post['post_user']);
			}
		}
		else
		{
			// if the user-experience is not counted in one of the forums we have to change the
			// experience of the user who have posted
			$user_exp = array();
			foreach($post_data as $pdata)
			{
				if($pdata['post_user'] > 0)
				{
					if(!isset($user_exp[$pdata['post_user']]))
						$user_exp[$pdata['post_user']] = 0;
					$user_exp[$pdata['post_user']] += BS_EXPERIENCE_FOR_POST;
				}
			}

			// if the target-forum counts the experience and the original forum not
			// we have to increase the experience for the topic-creator and all posters
			if(!$forum_data->get_increase_experience() && $target_forum_data->get_increase_experience())
			{
				// just increase the topic-creation-experience if we split the posts (create a new topic)
				if($type == 'split')
				{
					BS_DAO::get_profile()->update_user_by_id(
						array('exppoints' => array('exppoints + '.BS_EXPERIENCE_FOR_TOPIC)),
						$first_post['post_user']
					);
				}

				foreach($user_exp as $user_id => $points)
				{
					BS_DAO::get_profile()->update_user_by_id(
						array('exppoints' => array('exppoints + '.$points)),$user_id
					);
				}
			}
			// the other way arround.
			else if($forum_data->get_increase_experience() &&
				!$target_forum_data->get_increase_experience())
			{
				// if we move the complete topic we must substract the experience for the topic
				// because the other forum does not count the experience
				if($complete_move)
				{
					BS_DAO::get_profile()->update_user_by_id(
						array('exppoints' => array('exppoints - '.BS_EXPERIENCE_FOR_TOPIC)),
						$first_post['post_user']
					);
				}
				// we have to substract the experience of the posters
				// NOTE: we don't reduce the experience for the first post, because it is not possible
				// to move the first post in a topic. therefore there is nothing to do

				foreach($user_exp as $user_id => $points)
				{
					BS_DAO::get_profile()->update_user_by_id(
						array('exppoints' => array('exppoints - '.$points)),$user_id
					);
				}
			}
		}

		// move the posts
		BS_DAO::get_posts()->update_by_ids($post_ids,array(
			'rubrikid' => $target_fid,
			'threadid' => $topic_id
		));

		// update attachments
		BS_DAO::get_attachments()->update_topic_id($post_ids,$topic_id);

		// remove the topic?
		if($complete_move)
		{
			// create and check plain-action
			$deltopics = new BS_Front_Action_Plain_DeleteTopics(array($tid),$fid,false,false);
			$res = $deltopics->check_data();
			// note that this may not happen
			if($res != '')
				return $res;
			
			// delete the topic
			$deltopics->perform_action();
		}

		if($complete_move)
		{
			$murl = BS_URL::get_url(
				'posts','&amp;'.BS_URL_FID.'='.$target_fid.'&amp;'.BS_URL_TID.'='.$topic_id
			);
			$this->add_link($locale->lang('go_to_new_topic'),$murl);
		}
		else
		{
			$murl = BS_URL::get_url(
				'posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid
			);
			$this->add_link($locale->lang('go_to_topic'),$murl);
		}
		
		$this->set_action_performed(true);

		return '';
	}
}
?>