<?php
/**
 * Contains the leave-event-action
 *
 * @version			$Id: action_leaveevent.php 769 2008-05-25 13:38:35Z nasmussen $
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
		// is the user loggedin?
		if($this->cfg['enable_calendar_events'] == 0 || !$this->user->is_loggedin())
			return 'Calendar-events disabled or not loggedin';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check parameters
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		if($id == null)
			return 'The id "'.$id.'" is invalid';

		// does the topic exist?
		$data = BS_DAO::get_events()->get_by_id($id);
		if($data === false || $data['max_announcements'] < 0)
			return 'No calendar-event with id "'.$id.'" and allowed announcements found';

		// is the user announced to this event?
		if(!BS_DAO::get_eventann()->is_announced($this->user->get_user_id(),$id))
			return 'You are not announced';

		$timeout = ($data['timeout'] == 0) ? $data['event_begin'] : $data['timeout'];
		if(time() > $timeout)
			return 'topic_closed';

		BS_DAO::get_eventann()->leave($this->user->get_user_id(),$id);

		$url = $this->url->get_url('calendar','&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_ID.'='.$id);
		$this->add_link($this->locale->lang('back'),$url);
		$this->set_action_performed(true);

		return '';
	}
}
?>