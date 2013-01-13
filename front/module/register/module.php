<?php
/**
 * Contains the register-module
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
 * The register-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_register extends BS_Front_Module
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
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$renderer = $doc->use_default_renderer();
		$com = BS_Community_Manager::get_instance();
		
		$renderer->set_has_access($cfg['enable_registrations'] && !$user->is_loggedin() &&
			$com->is_registration_enabled());
		
		$renderer->add_action(BS_ACTION_REGISTER,'default');

		$renderer->add_breadcrumb($locale->lang('register'),BS_URL::build_mod_url());
	}
	
	/**
	 * @see BS_Front_Module::is_guest_only()
	 * @return boolean
	 */
	public function is_guest_only()
	{
		return true;
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		$form = $this->request_formular(false);

		$tpl->add_variables(array(
			'account_activation' => $cfg['account_activation'],
			'user_name_size' => max(10,min(50,$cfg['profile_max_user_len'])),
			'user_name_maxlength' => $cfg['profile_max_user_len'],
			'password_size' => max(10,min(50,$cfg['profile_max_pw_len'])),
			'password_maxlength' => $cfg['profile_max_pw_len'],
			'target_url' => BS_URL::build_mod_url(),
			'action_type' => BS_ACTION_REGISTER
		));
		
		$tplfields = array();
		$cfields = BS_AddField_Manager::get_instance();
		foreach($cfields->get_fields_at(BS_UF_LOC_REGISTRATION) as $field)
		{
			$value = $field->get_value_from_formular();

			$tplfields[] = array(
				'required_field' => $field->get_data()->is_required() ? ' *' : '',
				'field_name' => $field->get_title(),
				'field_value' => $field->get_formular_field($form,$value)
			);
		}

		$sec_code_field = FWS_StringHelper::generate_random_key(15);
		$user->set_session_data('sec_code_field',$sec_code_field);
		
		$email_display_mode_options = array(
			'hide' => $locale->lang('email_display_mode_hide'),
			'jumble' => $locale->lang('email_display_mode_jumble'),
			'default' => $locale->lang('email_display_mode_default')
		);

		$tpl->add_variable_ref('add_fields',$tplfields);
		$tpl->add_variables(array(
			'email_display_mode_options' => $email_display_mode_options,
			'enable_security_code' => $cfg['enable_security_code'] == 1,
			'security_code_img' => BS_URL::build_standalone_url('security_code'),
			'enable_board_emails' => $cfg['enable_emails'] == 1,
			'enable_pms' => $cfg['enable_pms'] == 1,
			'sec_code_field' => $sec_code_field
		));
	}
}
?>