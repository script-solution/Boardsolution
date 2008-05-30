<?php
/**
 * Contains the delete-post-action
 *
 * @version			$Id: action_default.php 741 2008-05-24 12:04:56Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-post-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_delete_post_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		if(!$this->input->isset_var('submit','post'))
			return '';

		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);

		// check ids of the posts to delete
		$ids = $this->input->get_var('selected_posts','post');
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			return 'split_no_posts_selected';

		// check other parameters
		if($fid == null || $tid == null)
			return 'Invalid forum-id or topic-id';

		// does the forum exist?
		$forum_data = $this->forums->get_node_data($fid);
		if($forum_data === null)
			return 'Forum with id "'.$fid.'" not found';

		// forum closed?
		if(!$this->user->is_admin() && $this->forums->forum_is_closed($fid))
			return 'You are no admin and the forum is closed';

		// does the topic exist?
		$topic_data = $this->cache->get_cache('topic')->current();
		if($topic_data == null)
			return 'The topic has not been found';

		// topic closed?
		if($topic_data['thread_closed'] == 1 && !$this->user->is_admin())
			return 'You are no admin and the topic is closed';

		// collect deleteable posts
		$first_post = BS_DAO::get_posts()->get_first_postid_in_topic($fid,$tid);

		$selected_posts = array();
		$order = BS_PostingUtils::get_instance()->get_posts_order();
		foreach(BS_DAO::get_posts()->get_posts_from_topic($ids,$fid,$tid,'p.id',$order) as $data)
		{
			// ensure that nobody can perform an action with the first post of a topic
			if($data['id'] == $first_post)
				continue;

			// check if the user has permission to perform the action
			if(!$this->auth->has_current_forum_perm(BS_MODE_DELETE_POSTS,$data['post_user']))
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
		
		$forum_data = $this->forums->get_node_data($fid);

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
			BS_UnreadUtils::get_instance()->remove_posts($post_ids,$tid);
			
			// remove attachments
			foreach(BS_DAO::get_attachments()->get_by_postids($post_ids) as $adata)
				$this->functions->delete_attachment($adata['attachment_path']);

			BS_DAO::get_attachments()->delete_by_postids($post_ids);
			
			// remove the posts
			BS_DAO::get_posts()->delete_by_ids($post_ids);
		}

		BS_Front_Action_Helper::get_instance()->adjust_last_post_time($max_post_time);
		
		$lastpost = BS_DAO::get_posts()->get_lastpost_data_in_topic($tid);
		$header_add = '';
		if(BS_PostingUtils::get_instance()->get_posts_order() == 'ASC')
		{
			$post_num = BS_DAO::get_posts()->get_count_in_topic($tid);
			if($post_num > $this->cfg['posts_per_page'])
			{
				$pagination = new BS_Pagination($this->cfg['posts_per_page'],$post_num);
				$header_add = '&'.BS_URL_SITE.'='.$pagination->get_page_count();
			}
		}
		
		$url = $this->url->get_url(
			'posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.$header_add
		).'#b_'.$lastpost['id'];
		$this->add_link($this->locale->lang('go_to_topic'),$url);
		$this->set_action_performed(true);

		return '';
	}
}
?>