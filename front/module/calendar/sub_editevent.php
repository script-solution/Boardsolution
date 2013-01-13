<?php
/**
 * Contains the editevent-calendar-submodule
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * The editevent submodule for module calendar
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_calendar_editevent extends BS_Front_SubModule
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
		$renderer->add_action(BS_ACTION_CAL_ADD_EVENT,'addevent');
		$renderer->add_action(BS_ACTION_CAL_EDIT_EVENT,'editevent');
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$url = BS_URL::get_sub_url(0,'editevent');
		if($id !== null)
			$renderer->add_breadcrumb($locale->lang('edit_event'),$url->set(BS_URL_ID,$id)->to_url());
		else
			$renderer->add_breadcrumb($locale->lang('add_event'),$url->to_url());
	}
	
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$input = FWS_Props::get()->input();
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
			
			$url = BS_URL::get_sub_url(0,'eventdetails');
			$url->set(BS_URL_ID,$id);
			$back_url = $url->to_url();
			
			$target_url = BS_URL::get_sub_url(0,'editevent')->set(BS_URL_ID,$id)->to_url();
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
			
			$back_url = BS_URL::build_mod_url();
			$target_url = BS_URL::build_sub_url(0,'editevent');
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
			BS_PostingUtils::add_post_preview('desc',1,1);
		
		$form = $this->request_formular(false,true);

		$pform = new BS_PostingForm(
			$locale->lang('description').':',$default['description_posted'],'desc'
		);
		$pform->set_textarea_height('100px');
		$pform->add_form();
		
		$tpl->add_variable_ref('default',$default);
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
