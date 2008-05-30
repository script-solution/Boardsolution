<?php
/**
 * Contains the reply-action
 *
 * @version			$Id: action_default.php 728 2008-05-22 22:09:30Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The reply-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_new_post_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// anything to do?
		if(!$this->input->isset_var('submit','post'))
			return '';

		// has the user the permission to reply in this forum?
		if(!$this->auth->has_current_forum_perm(BS_MODE_REPLY))
			return 'You have no permission to reply in this forum';

		// are all parameters valid?
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		
		// closed?
		$forum_data = $this->forums->get_node_data($fid);
		if(!$this->user->is_admin() && $forum_data->get_forum_is_closed())
			return 'You are no admin and the forum is closed';

		// check if the user has just made a post
		$spam_post_on = $this->auth->is_ipblock_enabled('spam_post');
		if($spam_post_on)
		{
			if($this->ips->entry_exists('post'))
				return sprintf($this->locale->lang('error_postipsperre'),($this->ips->get_timeout('post') / 60));
		}

		// does the topic exist and is it open?
		$topic_data = BS_DAO::get_topics()->get_by_id($tid);
		if(!$this->user->is_admin() && $topic_data['thread_closed'] == 1)
			return 'You are no admin and the topic is closed';

		// build plain-action
		$post = BS_Front_Action_Plain_Post::get_default($fid,$tid,false);
		
		// check the data
		$res = $post->check_data();
		if($res != '')
			return $res;
		
		// check attachments
		$attachments = $this->user->is_loggedin() && $this->auth->has_global_permission('attachments_add');
		if($attachments)
		{
			$att = BS_Front_Action_Plain_Attachments::get_default();
			$res = $att->check_data();
			// we don't want to abort here, we just skip the attachments
			if($res != '')
				$attachments = false;
		}
		
		// perform actions
		$post->perform_action();
		if($attachments)
		{
			$att->set_target($post->get_post_id(),$tid);
			$att->perform_action();
		}

		$this->ips->add_entry('post');

		// generate the redirect-url
		$header_add = '';
		if(BS_PostingUtils::get_instance()->get_posts_order() == 'ASC')
		{
			$post_num = BS_DAO::get_posts()->get_count_in_topic($tid);
			if($post_num > $this->cfg['posts_per_page'])
			{
				$params = $this->functions->get_page_params($this->cfg['posts_per_page'],$post_num);
				$header_add = '&'.BS_URL_SITE.'='.$params['final'];
			}
		}

		$url = $this->url->get_url(
			'posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$tid.$header_add
		).'#b_'.$post->get_post_id();
		$this->add_link($this->locale->lang('go_to_post'),$url);
		$this->set_action_performed(true);

		return '';
	}
}
?>