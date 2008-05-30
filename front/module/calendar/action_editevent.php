<?php
/**
 * Contains the edit-event-action
 *
 * @version			$Id: action_editevent.php 735 2008-05-23 07:49:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_calendar_editevent extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// check parameter
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';

		// check if the event exists
		$event_data = BS_DAO::get_events()->get_by_id($id);
		if($event_data === false || $event_data['tid'] != 0)
			return 'Event with id "'.$id.'" not found or no calendar-event';

		// check permission
		if(!$this->user->is_admin())
		{
			if($event_data['user_id'] != $this->user->get_user_id() ||
				!$this->auth->has_global_permission('edit_cal_event'))
				return 'Not your own event or no permission to edit events';
		}

		// is the topic or the location empty?
		$topic_name = $this->input->get_var('topic_name','post',PLIB_Input::STRING);
		$location = $this->input->get_var('location','post',PLIB_Input::STRING);
		if(trim($topic_name) == '' || trim($location) == '')
			return 'terminleer';

		// get form variables
		$open_end = $this->input->get_var('open_end','post',PLIB_Input::STRING);
		$max_announcements = $this->input->get_var('max_announcements','post',PLIB_Input::INTEGER);
		$timeout_type = $this->input->correct_var('timeout_type','post',PLIB_Input::STRING,array('begin','custom'),'begin');
		$enable_announcements = $this->input->get_var('enable_announcements','post',PLIB_Input::INT_BOOL);
		$description = $this->input->get_var('description','post',PLIB_Input::STRING);

		$form = new BS_HTML_Formular();
		
		// check begin and end
		$begin = $form->get_date_chooser_timestamp('b_');
		if($open_end == 1)
			$end = 0;
		else
			$end = $form->get_date_chooser_timestamp('e_');

		if($end <= $begin && $open_end == null)
			return 'endekbeginn';

		if($timeout_type == 'begin')
			$timeout = 0;
		else
			$timeout = $form->get_date_chooser_timestamp('c_');

		if(!$enable_announcements)
			$max_announcements = -1;
		else
			$max_announcements = ($max_announcements < 0) ? 0 : $max_announcements;

		// edit event
		$fields = array(
			'event_title' => $topic_name,
			'event_begin' => $begin,
			'event_end' => $end,
			'timeout' => $timeout,
			'max_announcements' => $max_announcements,
			'description' => $description,
			'event_location' => $location
		);
		BS_DAO::get_events()->update($id,$fields);

		$url = $this->url->get_url('calendar','&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_ID.'='.$id);
		$this->add_link($this->locale->lang('back'),$url);
		$this->set_action_performed(true);

		return '';
	}
}
?>