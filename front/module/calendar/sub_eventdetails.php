<?php
/**
 * Contains the eventdetails-calendar-submodule
 * 
 * @version			$Id: sub_eventdetails.php 769 2008-05-25 13:38:35Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The eventdetails submodule for module calendar
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_calendar_eventdetails extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_CAL_JOIN_EVENT => 'joinevent',
			BS_ACTION_CAL_LEAVE_EVENT => 'leaveevent'
		);
	}
	
	public function run()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
	
		$event_data = BS_DAO::get_events()->get_by_id($id);
		
		// does the event exist?
		if($event_data === false)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,'');
			return;
		}
	
		// check permission
		$mode = $this->input->get_var(BS_URL_MODE,'get',PLIB_Input::STRING);
		if($mode == 'delete')
		{
			if(!$this->user->is_admin())
			{
				if($event_data['user_id'] != $this->user->get_user_id() ||
					!$this->auth->has_global_permission('delete_cal_event'))
				{
					$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS,'');
					return;
				}
			}
			
			$message = sprintf($this->locale->lang('delete_event_msg'),$event_data['event_title']);
			$yes_url = $this->url->get_url(
				'calendar','&amp;'.BS_URL_AT.'='.BS_ACTION_CAL_DEL_EVENT.'&amp;'.BS_URL_DEL.'='.$id,'&amp;',true
			);
			$no_url = $this->url->get_url(
				'calendar','&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_ID.'='.$id
			);
			$target = $this->url->get_url(
				'redirect','&amp;'.BS_URL_LOC.'=del_cal_event&amp;'.BS_URL_ID.'='.$id
			);
			
			$this->functions->add_delete_message($message,$yes_url,$no_url,$target);
		}
		
		if($event_data['event_end'] == 0)
			$event_end = 'open';
		else
			$event_end = PLIB_Date::get_date($event_data['event_end']);
		
		if($event_data['timeout'] == 0)
			$timeout = PLIB_Date::get_date($event_data['event_begin']);
		else
			$timeout = PLIB_Date::get_date($event_data['timeout']);
		
		$this->tpl->add_variables(array(
			'event_title' => $event_data['event_title'],
			'location' => $event_data['event_location'],
			'event_begin' => PLIB_Date::get_date($event_data['event_begin']),
			'event_end' => $event_end,
			'description' => nl2br($event_data['description'])
		));
		
		if($event_data['max_announcements'] >= 0)
		{
			$event = new BS_Event($event_data);
			$this->tpl->add_variables(array(
				'id' => $event_data['id'],
				'can_leave' => $event->can_leave(),
				'can_announce' => $event->can_announce(),
				'announcement_list' => $event->get_announcement_list(),
				'max_announcements' => $event_data['max_announcements'],
				'total_announcements' => $event->get_count(),
				'timeout' => $timeout
			));
		}
		
		$delete_perm = $this->cfg['display_denied_options'] || $this->auth->has_global_permission('delete_cal_event');
		$edit_perm = $this->cfg['display_denied_options'] || $this->auth->has_global_permission('edit_cal_event');
		$this->tpl->add_variables(array(
			'announcements_enabled' => $event_data['max_announcements'] >= 0,
			'display_edit_event' => $edit_perm,
			'display_delete_event' => $delete_perm,
			'edit_event' => $this->url->get_url(
				'calendar','&amp;'.BS_URL_LOC.'=editevent&amp;'.BS_URL_ID.'='.$id
			),
			'delete_event' => $this->url->get_url(
				'calendar','&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_MODE.'=delete&amp;'.BS_URL_ID.'='.$id
			)
		));
	}
	
	public function get_location()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		return array(
			$this->locale->lang('event_details') => $this->url->get_url(
				0,'&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_ID.'='.$id
			)
		);
	}
}
?>