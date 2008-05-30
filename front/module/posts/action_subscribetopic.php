<?php
/**
 * Contains the subscribe-topic-action
 *
 * @version			$Id: action_subscribetopic.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The subscribe-topic-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_posts_subscribetopic extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// has the user the permission to subscribe the topic?
		if(!$this->user->is_loggedin() || $this->cfg['enable_email_notification'] == 0)
			return 'You are not loggedin or subscriptions are disabled';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// are the parameters valid?
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		if($fid == null || $tid == null)
			return 'The forum-id or topic-id is invalid';

		// forum not accessable or a category?
		$denied_forums = BS_ForumUtils::get_instance()->get_denied_forums(true);
		if(in_array($fid,$denied_forums))
			return 'The forum is denied for you or a category';

		$sub = BS_Front_Action_Plain_SubscribeTopic::get_default($tid);
		$res = $sub->check_data();
		if($res != '')
			return $res;
		
		$sub->perform_action();

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('back'),$this->url->get_posts_url($fid,$tid));
		$url = $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=topics');
		$this->add_link($this->locale->lang('to_profile_subscr'),$url);
		$this->set_success_msg(
			sprintf($this->locale->lang('subscription_desc_topic'),$sub->get_topic_name())
		);

		return '';
	}
}
?>