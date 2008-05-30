<?php
/**
 * Contains the join-event-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The join-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_posts_joinevent extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// is the user loggedin?
		if($this->cfg['enable_events'] == 0 || !$this->user->is_loggedin())
			return 'Events are disabled or you\'re not loggedin';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'The session-id is invalid';

		// check parameters
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);

		if($fid == null || $tid == null)
			return 'The forum-id or topic-id is invalid';

		// does the topic exist?
		$data = BS_DAO::get_topics()->get_by_id($tid);
		if($data === false)
			return 'A topic with id "'.$tid.'" has not been found';
		
		$event = BS_DAO::get_events()->get_by_topic_id($tid);
		if($event === false || $event['max_announcements'] < 0)
			return 'An event with topic-id "'.$tid.'" and enabled announcements has not been found';
		
		// is the user already announced to this event?
		if(BS_DAO::get_eventann()->is_announced($this->user->get_user_id(),$event['id']))
			return 'You are already announced';

		if($event['max_announcements'] > 0)
		{
			if(BS_DAO::get_eventann()->get_count_of_event($event['id']) >= $event['max_announcements'])
				return 'event_full';
		}

		$timeout = ($event['timeout'] == 0) ? $event['event_begin'] : $event['timeout'];
		if($data['thread_closed'] == 1 || time() > $timeout)
			return 'topic_closed';

		BS_DAO::get_eventann()->announce($this->user->get_user_id(),$event['id']);

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('go_to_topic'),$this->url->get_posts_url($fid,$tid));

		return '';
	}
}
?>