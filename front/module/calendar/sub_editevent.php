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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param FWS_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACTION_CAL_ADD_EVENT,'addevent');
		$renderer->add_action(BS_ACTION_CAL_EDIT_EVENT,'editevent');
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		if($id !== null)
		{
			$renderer->add_breadcrumb(
				$locale->lang('edit_event'),
				$url->get_url(0,'&amp;'.BS_URL_LOC.'=editevent&amp;'.BS_URL_ID.'='.$id)
			);
		}
		else
		{
			$renderer->add_breadcrumb(
				$locale->lang('add_event'),
				$url->get_url(0,'&amp;'.BS_URL_LOC.'=editevent')
			);
		}
	}
	
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$input = FWS_Props::get()->input();
		$url = FWS_Props::get()->url();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		// calendar-events disabled?
		if($cfg['enable_calendar_events'] == 0)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}
		
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$type = $id !== null ? 'edit' : 'add';
		
		if($type == 'edit')
		{
			$default = BS_DAO::get_events()->get_by_id($id);
			
			$back_url = $url->get_url(0,'&amp;'.BS_URL_LOC.'=eventdetails&amp;'.BS_URL_ID.'='.$id);
			$target_url = $url->get_url(0,'&amp;'.BS_URL_LOC.'=editevent&amp;'.BS_URL_ID.'='.$id);
		}
		else
		{
			$day = $input->get_var(BS_URL_DAY,'get',FWS_Input::INTEGER);
			$default = array(
				'user_id' => 0,
				'max_announcements' => 0,
				'description_posted' => '',
				'event_location' => '',
				'event_begin' => $day !== null ? $day : 0,
				'event_end' => $day !== null ? $day : 0,
				'timeout' => 0,
				'event_title' => ''
			);
			
			$back_url = $url->get_url();
			$target_url = $url->get_url(0,'&amp;'.BS_URL_LOC.'=editevent');
		}
		
		// check permission
		if(!$user->is_admin())
		{
			if($type == 'edit')
			{
				if($default['user_id'] != $user->get_user_id() ||
						!$auth->has_global_permission('edit_cal_event'))
				{
					$this->report_error(FWS_Document_Messages::NO_ACCESS);
					return;
				}
			}
			else
			{
				if(!$auth->has_global_permission('add_cal_event'))
				{
					$this->report_error(FWS_Document_Messages::NO_ACCESS);
					return;
				}
			}
		}

		if($input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview('desc',1,1);
		
		$form = $this->request_formular(false,true);

		$pform = new BS_PostingForm(
			$locale->lang('description').':',$default['description_posted'],'desc'
		);
		$pform->set_textarea_height('100px');
		$pform->add_form();
		
		$tpl->add_array('default',$default);
		$tpl->add_variables(array(
			'title' => $locale->lang($type.'_event'),
			'target_url' => $target_url,
			'action_type' => $type == 'add' ? BS_ACTION_CAL_ADD_EVENT : BS_ACTION_CAL_EDIT_EVENT,
			'open_end' => $form->get_checkbox_value('event_end',$default['event_end'] == 0),
			'timeout_type_begin' => $form->get_radio_value('timeout_type','begin',$default['timeout'] == 0),
			'timeout_type_self' => $form->get_radio_value('timeout_type','custom',$default['timeout'] != 0),
			'enable_announcements' => $default['max_announcements'] >= 0,
			'max_announcements' => max(0,$default['max_announcements']),
			'back_url' => $back_url
		));
	}
}
?>