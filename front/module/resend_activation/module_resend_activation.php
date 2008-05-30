<?php
/**
 * Contains the resend-activation-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The resend-activation-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_resend_activation extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_RESEND_ACT_LINK => 'default'
		);
	}
	
	public function run()
	{
		if(BS_ENABLE_EXPORT)
		{
			$this->_report_error();
			return;
		}
		
		$this->_request_formular(false,false);
		
		$sec_code_field = PLIB_StringHelper::generate_random_key(15);
		$this->user->set_session_data('sec_code_field',$sec_code_field);
		
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url('resend_activation'),
			'action_type' => BS_ACTION_RESEND_ACT_LINK,
			'enable_security_code' => $this->cfg['enable_security_code'] == 1,
			'security_code_img' => $this->url->get_standalone_url('front','security_code'),
			'sec_code_field' => $sec_code_field
		));
	}

	public function get_location()
	{
		return array($this->locale->lang('resend_activation_link') => $this->url->get_url(0));
	}

	public function has_access()
	{
		return !$this->user->is_loggedin();
	}

	public function is_guest_only()
	{
		return true;
	}
}
?>