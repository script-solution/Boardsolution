<?php
/**
 * Contains the eventdetails-calendar-submodule
 * 
 * @version			$Id$
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param PLIB_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACTION_CAL_JOIN_EVENT,'joinevent');
		$renderer->add_action(BS_ACTION_CAL_LEAVE_EVENT,'leaveevent');
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$renderer->add_breadcrumb(
			$locale->lang('event_details'),
			$url->get_url(0,'&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_ID.'='.$id)
		);
	}
	
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		$auth = PLIB_Props::get()->auth();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();

		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
	
		$event_data = BS_DAO::get_events()->get_by_id($id);
		
		// does the event exist?
		if($event_data === false)
		{
			$this->report_error(PLIB_Document_Messages::ERROR,'');
			return;
		}
	
		// check permission
		$mode = $input->get_var(BS_URL_MODE,'get',PLIB_Input::STRING);
		if($mode == 'delete')
		{
			if(!$user->is_admin())
			{
				if($event_data['user_id'] != $user->get_user_id() ||
					!$auth->has_global_permission('delete_cal_event'))
				{
					$this->report_error(PLIB_Document_Messages::NO_ACCESS,'');
					return;
				}
			}
			
			$message = sprintf($locale->lang('delete_event_msg'),$event_data['event_title']);
			$yes_url = $url->get_url(
				'calendar','&amp;'.BS_URL_AT.'='.BS_ACTION_CAL_DEL_EVENT.'&amp;'.BS_URL_DEL.'='.$id,'&amp;',true
			);
			$no_url = $url->get_url(
				'calendar','&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_ID.'='.$id
			);
			$target = $url->get_url(
				'redirect','&amp;'.BS_URL_LOC.'=del_cal_event&amp;'.BS_URL_ID.'='.$id
			);
			
			$functions->add_delete_message($message,$yes_url,$no_url,$target);
		}
		
		if($event_data['event_end'] == 0)
			$event_end = 'open';
		else
			$event_end = PLIB_Date::get_date($event_data['event_end']);
		
		if($event_data['timeout'] == 0)
			$timeout = PLIB_Date::get_date($event_data['event_begin']);
		else
			$timeout = PLIB_Date::get_date($event_data['timeout']);
		
		$bbcode = new BS_BBCode_Parser(
			$event_data['description'],'desc',true,true
		);
		$text = $bbcode->get_message_for_output();
		
		$tpl->add_variables(array(
			'event_title' => $event_data['event_title'],
			'location' => $event_data['event_location'],
			'event_begin' => PLIB_Date::get_date($event_data['event_begin']),
			'event_end' => $event_end,
			'description' => $text
		));
		
		if($event_data['max_announcements'] >= 0)
		{
			$event = new BS_Event($event_data);
			$tpl->add_variables(array(
				'id' => $event_data['id'],
				'can_leave' => $event->can_leave(),
				'can_announce' => $event->can_announce(),
				'announcement_list' => $event->get_announcement_list(),
				'max_announcements' => $event_data['max_announcements'],
				'total_announcements' => $event->get_count(),
				'timeout' => $timeout
			));
		}
		
		$delete_perm = $cfg['display_denied_options'] || $auth->has_global_permission('delete_cal_event');
		$edit_perm = $cfg['display_denied_options'] || $auth->has_global_permission('edit_cal_event');
		$tpl->add_variables(array(
			'announcements_enabled' => $event_data['max_announcements'] >= 0,
			'display_edit_event' => $edit_perm,
			'display_delete_event' => $delete_perm,
			'edit_event' => $url->get_url(
				'calendar','&amp;'.BS_URL_LOC.'=editevent&amp;'.BS_URL_ID.'='.$id
			),
			'delete_event' => $url->get_url(
				'calendar','&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_MODE.'=delete&amp;'.BS_URL_ID.'='.$id
			)
		));
	}
}
?>