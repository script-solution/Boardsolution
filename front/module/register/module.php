<?php
/**
 * Contains the register-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		
		$this->set_guest_only(true);
		
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