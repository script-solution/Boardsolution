<?php
/**
 * Contains the register-module
 * 
 * @version			$Id: module_register.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The register-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_register extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_REGISTER => 'default'
		);
	}
	
	public function run()
	{
		$form = $this->_request_formular(false);

		$this->tpl->add_variables(array(
			'account_activation' => $this->cfg['account_activation'],
			'user_name_size' => max(10,min(50,$this->cfg['profile_max_user_len'])),
			'user_name_maxlength' => $this->cfg['profile_max_user_len'],
			'password_size' => max(10,min(50,$this->cfg['profile_max_pw_len'])),
			'password_maxlength' => $this->cfg['profile_max_pw_len'],
			'target_url' => $this->url->get_url(0),
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

		$sec_code_field = PLIB_StringHelper::generate_random_key(15);
		$this->user->set_session_data('sec_code_field',$sec_code_field);
		
		$email_display_mode_options = array(
			'hide' => $this->locale->lang('email_display_mode_hide'),
			'jumble' => $this->locale->lang('email_display_mode_jumble'),
			'default' => $this->locale->lang('email_display_mode_default')
		);

		$this->tpl->add_array('add_fields',$tplfields);
		$this->tpl->add_variables(array(
			'email_display_mode_options' => $email_display_mode_options,
			'enable_security_code' => $this->cfg['enable_security_code'] == 1,
			'security_code_img' => $this->url->get_standalone_url('front','security_code'),
			'enable_board_emails' => $this->cfg['enable_emails'] == 1,
			'enable_pms' => $this->cfg['enable_pms'] == 1,
			'sec_code_field' => $sec_code_field
		));
	}

	public function get_location()
	{
		return array($this->locale->lang('register') => $this->url->get_url('register'));
	}

	public function has_access()
	{
		return $this->cfg['enable_registrations'] && !$this->user->is_loggedin() && !BS_ENABLE_EXPORT;
	}

	public function is_guest_only()
	{
		return true;
	}
}
?>