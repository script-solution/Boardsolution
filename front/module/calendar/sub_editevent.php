<?php
/**
 * Contains the editevent-calendar-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The editevent submodule for module calendar
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_calendar_editevent extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_CAL_ADD_EVENT => 'addevent',
			BS_ACTION_CAL_EDIT_EVENT => 'editevent'
		);
	}
	
	public function run()
	{
		// calendar-events disabled?
		if($this->cfg['enable_calendar_events'] == 0)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}
		
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$type = $id !== null ? 'edit' : 'add';
		
		if($type == 'edit')
		{
			$default = BS_DAO::get_events()->get_by_id($id);
			
			$back_url = $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_ID.'='.$id);
			$target_url = $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=editevent&amp;'.BS_URL_ID.'='.$id);
		}
		else
		{
			$day = $this->input->get_var(BS_URL_DAY,'get',PLIB_Input::INTEGER);
			$default = array(
				'user_id' => 0,
				'max_announcements' => 0,
				'description' => '',
				'event_location' => '',
				'event_begin' => $day !== null ? $day : 0,
				'event_end' => $day !== null ? $day : 0,
				'timeout' => 0,
				'event_title' => ''
			);
			
			$back_url = $this->url->get_url();
			$target_url = $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=editevent');
		}
		
		// check permission
		if(!$this->user->is_admin())
		{
			if($type == 'edit')
			{
				if($default['user_id'] != $this->user->get_user_id() ||
						!$this->auth->has_global_permission('edit_cal_event'))
				{
					$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
					return;
				}
			}
			else
			{
				if(!$this->auth->has_global_permission('add_cal_event'))
				{
					$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
					return;
				}
			}
		}
		
		$form = $this->_request_formular(false,false);
		
		$this->tpl->add_array('default',$default);
		$this->tpl->add_variables(array(
			'title' => $this->locale->lang($type.'_event'),
			'target_url' => $target_url,
			'action_type' => $type == 'add' ? BS_ACTION_CAL_ADD_EVENT : BS_ACTION_CAL_EDIT_EVENT,
			'open_end' => $form->get_checkbox_value('event_end',$default['event_end'] == 0),
			'timeout_type_begin' => $form->get_radio_value('timeout','begin',$default['timeout'] == 0),
			'timeout_type_self' => $form->get_radio_value('timeout','self',$default['timeout'] != 0),
			'enable_announcements' => $default['max_announcements'] >= 0,
			'max_announcements' => max(0,$default['max_announcements']),
			'back_url' => $back_url
		));
	}
	
	public function get_location()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		if($id !== null)
		{
			return array(
				$this->locale->lang('edit_event') => $this->url->get_url(
					0,'&amp;'.BS_URL_LOC.'=editevent&amp;'.BS_URL_ID.'='.$id
				)
			);
		}
		
		return array(
			$this->locale->lang('add_event') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=editevent')
		);
	}
}
?>