<?php
/**
 * Contains the leave-event-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The leave-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_calendar_leaveevent extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$functions = FWS_Props::get()->functions();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		// is the user loggedin?
		if($cfg['enable_calendar_events'] == 0 || !$user->is_loggedin())
			return 'Calendar-events disabled or not loggedin';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check parameters
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		if($id == null)
			return 'The id "'.$id.'" is invalid';

		// does the topic exist?
		$data = BS_DAO::get_events()->get_by_id($id);
		if($data === false || $data['max_announcements'] < 0)
			return 'No calendar-event with id "'.$id.'" and allowed announcements found';

		// is the user announced to this event?
		if(!BS_DAO::get_eventann()->is_announced($user->get_user_id(),$id))
			return 'You are not announced';

		$timeout = ($data['timeout'] == 0) ? $data['event_begin'] : $data['timeout'];
		if(time() > $timeout)
			return 'topic_closed';

		BS_DAO::get_eventann()->leave($user->get_user_id(),$id);

		$murl = BS_URL::get_sub_url('calendar','eventdetails');
		$murl->set(BS_URL_ID,$id);
		$this->add_link($locale->lang('back'),$murl);
		$this->set_action_performed(true);

		return '';
	}
}
?>