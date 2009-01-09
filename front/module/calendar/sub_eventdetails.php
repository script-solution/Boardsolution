<?php
/**
 * Contains the eventdetails-calendar-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The eventdetails submodule for module calendar
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_calendar_eventdetails extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACTION_CAL_JOIN_EVENT,'joinevent');
		$renderer->add_action(BS_ACTION_CAL_LEAVE_EVENT,'leaveevent');
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$url = BS_URL::get_sub_url();
		$url->set(BS_URL_ID,$id);
		$renderer->add_breadcrumb($locale->lang('event_details'),$url->to_url());
	}
	
	public function run()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
	
		$event_data = BS_DAO::get_events()->get_by_id($id);
		
		// does the event exist?
		if($event_data === false)
		{
			$this->report_error(FWS_Document_Messages::ERROR,'');
			return;
		}
	
		// check permission
		$mode = $input->get_var(BS_URL_MODE,'get',FWS_Input::STRING);
		if($mode == 'delete')
		{
			if(!$user->is_admin())
			{
				if($event_data['user_id'] != $user->get_user_id() ||
					!$auth->has_global_permission('delete_cal_event'))
				{
					$this->report_error(FWS_Document_Messages::NO_ACCESS,'');
					return;
				}
			}
			
			$message = sprintf($locale->lang('delete_event_msg'),$event_data['event_title']);
			$yes_url = BS_URL::get_mod_url();
			$yes_url->set(BS_URL_AT,BS_ACTION_CAL_DEL_EVENT);
			$yes_url->set(BS_URL_DEL,$id);
			$yes_url->set_sid_policy(BS_URL::SID_FORCE);
			
			$url = BS_URL::get_sub_url();
			$url->set(BS_URL_ID,$id);
			$no_url = $url->to_url();
			
			$url->set(BS_URL_ACTION,'redirect');
			
			$url = BS_URL::get_mod_url('redirect');
			$target = $url->set(BS_URL_LOC,'del_cal_event')->to_url();
			
			$functions->add_delete_message($message,$yes_url->to_url(),$no_url,$target);
		}
		
		if($event_data['event_end'] == 0)
			$event_end = 'open';
		else
			$event_end = FWS_Date::get_date($event_data['event_end']);
		
		if($event_data['timeout'] == 0)
			$timeout = FWS_Date::get_date($event_data['event_begin']);
		else
			$timeout = FWS_Date::get_date($event_data['timeout']);
		
		$bbcode = new BS_BBCode_Parser(
			$event_data['description'],'desc',true,true
		);
		$text = $bbcode->get_message_for_output();
		
		$tpl->add_variables(array(
			'event_title' => $event_data['event_title'],
			'location' => $event_data['event_location'] ?
				$event_data['event_location'] : $locale->lang('notavailable'),
			'event_begin' => FWS_Date::get_date($event_data['event_begin']),
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
		
		$url = BS_URL::get_sub_url();
		$url->set(BS_URL_ID,$id);
		$url->set(BS_URL_MODE,'delete');
		
		$tpl->add_variables(array(
			'announcements_enabled' => $event_data['max_announcements'] >= 0,
			'display_edit_event' => $edit_perm,
			'display_delete_event' => $delete_perm,
			'edit_event' => BS_URL::get_sub_url(0,'editevent')->set(BS_URL_ID,$id)->to_url(),
			'delete_event' => $url->to_url()
		));
	}
}
?>