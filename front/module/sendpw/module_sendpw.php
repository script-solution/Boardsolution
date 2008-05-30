<?php
/**
 * Contains the sendpw-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The sendpw-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_sendpw extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_SEND_PW => 'default'
		);
	}

	public function run()
	{
		if(BS_ENABLE_EXPORT && BS_EXPORT_SEND_PW_TYPE != 'enabled')
		{
			$this->_report_error();
			return;
		}
		
		$this->_request_formular(false,false);
		
		$sec_code_field = PLIB_StringHelper::generate_random_key(15);
		$this->user->set_session_data('sec_code_field',$sec_code_field);
		
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url('sendpw'),
			'action_type' => BS_ACTION_SEND_PW,
			'enable_security_code' => $this->cfg['enable_security_code'] == 1,
			'security_code_img' => $this->url->get_standalone_url('front','security_code'),
			'sec_code_field' => $sec_code_field
		));
	}

	public function get_location()
	{
		return array($this->locale->lang('forgetpw') => $this->url->get_url('sendpw'));
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