<?php
/**
 * Contains the delete-event-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-event-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_calendar_deleteevent extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();

		// check parameter
		$id = $input->get_var(BS_URL_DEL,'get',FWS_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';

		// check if the session-id is valid
		if(!$functions->has_valid_get_sid())
			return 'Invalid session-id';

		// check if the event exists
		$event_data = BS_DAO::get_events()->get_by_id($id);
		if($event_data === false || $event_data['tid'] != 0)
			return 'Event with id "'.$id.'" not found or no calendar-event';

		// check permission
		if(!$user->is_admin())
		{
			if($event_data['user_id'] != $user->get_user_id() ||
				!$auth->has_global_permission('delete_cal_event'))
				return 'Not your own event or no permission to delete events';
		}

		// delete the event
		BS_DAO::get_events()->delete_by_ids(array($id));

		$this->add_link($locale->lang('back'),$url->get_url('calendar'));
		$this->set_action_performed(true);

		return '';
	}
}
?>