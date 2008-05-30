<?php
/**
 * Contains the new-topic-action
 *
 * @version			$Id: action_default.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The new-topic-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_new_topic_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// nothing to do?
		if(!$this->input->isset_var('submit','post'))
			return '';

		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		
		// closed?
		$forum_data = $this->forums->get_node_data($fid);
		if(!$this->user->is_admin() && $forum_data->get_forum_is_closed())
			return 'You are no admin and the forum is closed';

		// check if the user has permission to start a topic
		if(!$this->auth->has_current_forum_perm(BS_MODE_START_TOPIC))
			return 'You have no permission to start topics in this forum';
		
		// check security-code
		if(!$this->user->is_loggedin())
		{
			if($this->cfg['use_captcha_for_guests'] == 1 && !$this->functions->check_security_code(false))
				return 'invalid_security_code';
		}

		// has the user just created a topic?
		$spam_thread_on = $this->auth->is_ipblock_enabled('spam_thread');
		if($spam_thread_on)
		{
			if($this->ips->entry_exists('topic'))
			{
				return sprintf(
					$this->locale->lang('error_threadpollipsperre'),$this
					->ips->get_timeout('topic') / 60
				);
			}
		}
		
		// build plain actions
		$post = BS_Front_Action_Plain_Post::get_default($fid);
		$topic = BS_Front_Action_Plain_Topic::get_default($post);
		
		// check the data
		$res = $topic->check_data();
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
		
		// check subscriptions
		$subscribe = $this->input->get_var('subscribe_topic','post',PLIB_Input::INT_BOOL);
		if($subscribe)
		{
			$sub = BS_Front_Action_Plain_SubscribeTopic::get_default($topic->get_topic_id(),false);
			$res = $sub->check_data();
			if($res != '')
				return $res;
		}
		
		// perform actions
		$topic->perform_action();
		if($subscribe)
			$sub->perform_action();
		if($attachments)
		{
			$att->set_target($post->get_post_id(),$topic->get_topic_id());
			$att->perform_action();
		}

		$this->ips->add_entry('topic');

		$url = $this->url->get_url(
			'posts','&amp;'.BS_URL_FID.'='.$fid.'&amp;'.BS_URL_TID.'='.$topic->get_topic_id()
		);
		$this->add_link($this->locale->lang('go_to_created_topic'),$url);
		$this->set_action_performed(true);

		return '';
	}
}
?>