<?php
/**
 * Contains the subscribe-forum-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The subscribe-forum-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_topics_subscribeforum extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// has the user the permission to subscribe the forum?
		if(!$this->user->is_loggedin() || $this->cfg['enable_email_notification'] == 0 ||
			 !$this->auth->has_global_permission('subscribe_forums'))
			return 'You are a guest, subscriptions are disabled or you can\'t subscribe to forums';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// is the parameter valid?
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		if($fid == null)
			return 'The forum-id "'.$fid.'" is invalid';

		// check if the forum exists
		$data = $this->forums->get_node_data($fid);
		if($data === null)
			return 'A forum with id "'.$fid.'" doesn\'t exist';

		// forum not accessable or a category?
		$denied_forums = BS_ForumUtils::get_instance()->get_denied_forums(true);
		if(in_array($fid,$denied_forums))
			return 'The forum is denied for you or a category';

		// has the user already subscribed this forum?
		if(BS_DAO::get_subscr()->has_subscribed_forum($this->user->get_user_id(),$fid))
			return 'already_subscribed_forum';

		// check if the user is allowed to subscribe this topic
		if($this->cfg['max_forum_subscriptions'] > 0)
		{
			$subscriptions = BS_DAO::get_subscr()->get_subscr_forums_count($this->user->get_user_id());
			if($subscriptions >= $this->cfg['max_forum_subscriptions'])
				return sprintf($this->locale->lang('error_max_forum_subscriptions'),
											 $this->cfg['max_forum_subscriptions']);
		}

		BS_DAO::get_subscr()->subscribe_forum($fid,$this->user->get_user_id());
		
		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('back'),$this->url->get_topics_url($fid));
		$url = $this->url->get_url('userprofile','&amp;'.BS_URL_LOC.'=forums');
		$this->add_link($this->locale->lang('to_profile_subscr'),$url);
		$this->set_success_msg(sprintf($this->locale->lang('subscription_desc_forum'),$data->get_name()));

		return '';
	}
}
?>