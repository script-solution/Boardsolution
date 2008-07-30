<?php
/**
 * Contains the new-poll-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The new-poll-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_new_poll_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$cfg = FWS_Props::get()->cfg();
		$ips = FWS_Props::get()->ips();
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// is the forum-id valid?
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		
		// closed?
		$forum_data = $forums->get_node_data($fid);
		if(!$user->is_admin() && $forum_data->get_forum_is_closed())
			return 'You are no admin and the forum is closed';

		// check if the user has permission to start a poll in this forum
		if(!$auth->has_current_forum_perm(BS_MODE_START_POLL) || $cfg['enable_polls'] == 0)
			return 'Polls are disabled or you have no permission to start them';

		// has the user just created a topic?
		$spam_thread_on = $auth->is_ipblock_enabled('spam_thread');
		if($spam_thread_on)
		{
			if($ips->entry_exists('topic'))
				return 'threadpollipsperre';
		}

		// build and check poll
		$options = $input->get_var('polloptions','post',FWS_Input::STRING);
		$multichoice = $input->get_var('multichoice','post',FWS_Input::INT_BOOL);
		$poll = new BS_Front_Action_Plain_Poll($options,$multichoice);
		$res = $poll->check_data();
		if($res != '')
			return $res;
		
		// build plain actions
		$post = BS_Front_Action_Plain_Post::get_default($fid);
		$topic = BS_Front_Action_Plain_Topic::get_default($post,$poll->get_poll_id());
		
		// check the data
		$res = $topic->check_data();
		if($res != '')
			return $res;
		
		// check attachments
		$attachments = $user->is_loggedin() && $auth->has_global_permission('attachments_add');
		if($attachments)
		{
			$att = BS_Front_Action_Plain_Attachments::get_default();
			$res = $att->check_data();
			// we don't want to abort here, we just skip the attachments
			if($res != '')
				$attachments = false;
		}
		
		// check subscriptions
		$subscribe = $input->get_var('subscribe_topic','post',FWS_Input::INT_BOOL);
		if($subscribe)
		{
			$sub = BS_Front_Action_Plain_SubscribeTopic::get_default($topic->get_topic_id(),false);
			$res = $sub->check_data();
			if($res != '')
				return $res;
		}
		
		// perform actions
		$poll->perform_action();
		$topic->perform_action();
		if($subscribe)
			$sub->perform_action();
		if($attachments)
		{
			$att->set_target($post->get_post_id(),$topic->get_topic_id());
			$att->perform_action();
		}

		$ips->add_entry('topic');

		$murl = $url->get_url(
			'posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$topic->get_topic_id()
		);
		$this->add_link($locale->lang('go_to_created_topic'),$murl);
		$this->set_action_performed(true);

		return '';
	}
}
?>