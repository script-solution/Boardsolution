<?php
/**
 * Contains the infos-userprofile-submodule
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
 * The infos submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_infos extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_EDIT_PERS_PROFILE,'updateinfos');

		$renderer->add_breadcrumb($locale->lang('personal_info'),BS_URL::build_sub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		
		if($input->get_var(BS_URL_MODE,'get') == 1)
			$msgs->add_notice($locale->lang('fill_required_fields_notice'));
		
		$form = $this->request_formular(false);
		
		$email_change = BS_DAO::get_changeemail()->get_by_user($user->get_user_id());
		$com = BS_Community_Manager::get_instance();
		
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_EDIT_PERS_PROFILE,
			'show_email' => $com->is_user_management_enabled() && $cfg['allow_email_changes'],
			'email_value' => $form->get_input_value('user_email',$user->get_profile_val('user_email')),
			'target_url' => BS_URL::build_sub_url(),
			'confirm_emails' => $cfg['confirm_email_addresses'],
			'new_email_address' => $email_change['user_id'] ? $email_change['email_address'] : ''
		));
		
		$cfields = BS_AddField_Manager::get_instance();

		$additional_fields = array();
		foreach($cfields->get_fields_at(BS_UF_LOC_USER_PROFILE) as $field)
		{
			/* @var $field FWS_AddField_Field */
			$data = $field->get_data();
			$stored_val = $user->get_profile_val('add_'.$data->get_name());
			$value = $field->get_value_from_formular($stored_val);

			$additional_fields[] = array(
				'required_field' => $data->is_required() ? ' *' : '',
				'field_name' => $field->get_title(),
				'field_value' => $field->get_formular_field($form,$value)
			);
		}
		
		$tpl->add_variable_ref('additional_fields',$additional_fields);
	}
}
?>
