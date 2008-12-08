<?php
/**
 * Contains the edit-event-action
 *
 * @version			$Id$
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
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post',FWS_Input::STRING))
			return '';
		
		// check parameter
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';

		// check if the event exists
		$event_data = BS_DAO::get_events()->get_by_id($id);
		if($event_data === false || $event_data['tid'] != 0)
			return 'Event with id "'.$id.'" not found or no calendar-event';

		// check permission
		if(!$user->is_admin())
		{
			if($event_data['user_id'] != $user->get_user_id() ||
				!$auth->has_global_permission('edit_cal_event'))
				return 'Not your own event or no permission to edit events';
		}

		// is the topic or the location empty?
		$topic_name = $input->get_var('topic_name','post',FWS_Input::STRING);
		$location = $input->get_var('location','post',FWS_Input::STRING);
		if(trim($topic_name) == '' || trim($location) == '')
			return 'terminleer';

		// get form variables
		$open_end = $input->get_var('open_end','post',FWS_Input::STRING);
		$max_announcements = $input->get_var('max_announcements','post',FWS_Input::INTEGER);
		$timeout_type = $input->correct_var('timeout_type','post',FWS_Input::STRING,array('begin','custom'),'begin');
		$enable_announcements = $input->get_var('enable_announcements','post',FWS_Input::INT_BOOL);
		$description_posted = $input->get_var('text','post',FWS_Input::STRING);

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

		$description = '';
		$error = BS_PostingUtils::prepare_message_for_db(
			$description,$description_posted,'desc',true,true
		);
		if($error != '')
			return $error;
		
		// edit event
		$fields = array(
			'event_title' => $topic_name,
			'event_begin' => $begin,
			'event_end' => $end,
			'timeout' => $timeout,
			'max_announcements' => $max_announcements,
			'description' => $description,
			'description_posted' => $description_posted,
			'event_location' => $location
		);
		BS_DAO::get_events()->update($id,$fields);

		$murl = BS_URL::get_sub_url('calendar','eventdetails');
		$murl->set(BS_URL_ID,$id);
		$this->add_link($locale->lang('back'),$murl);
		$this->set_action_performed(true);

		return '';
	}
}
?>