<?php
/**
 * Contains the change-password-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The change-password-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_change_password extends BS_Front_Module
{
	public function get_actions()
	{
		return array(
			BS_ACTION_CHANGE_PASSWORD => 'default'
		);
	}
	
	public function run()
	{
		if(BS_ENABLE_EXPORT && BS_EXPORT_SEND_PW_TYPE != 'enabled')
		{
			$this->_report_error();
			return;
		}

		$user_id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$user_key = $this->input->get_var(BS_URL_KW,'get',PLIB_Input::STRING);

		// check parameter
		if($user_id == null || $user_key == null)
		{
			$this->_report_error();
			return;
		}

		// check if the entry exists
		if(!BS_DAO::get_changepw()->exists($user_id,$user_key))
		{
			$this->_report_error();
			return;
		}

		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url(
				0,'&amp;'.BS_URL_ID.'='.$user_id.'&amp;'.BS_URL_KW.'='.$user_key
			),
			'action_type' => BS_ACTION_CHANGE_PASSWORD,
			'password_size' => max(10,min(50,$this->cfg['profile_max_pw_len'])),
			'password_maxlength' => $this->cfg['profile_max_pw_len']
		));
	}

	public function get_location()
	{
		$user_id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$user_key = $this->input->get_var(BS_URL_KW,'get',PLIB_Input::STRING);
		$url = $this->url->get_url(
			'change_password','&amp;'.BS_URL_ID.'='.$user_id.'&amp;'.BS_URL_KW.'='.$user_key
		);
		return array($this->locale->lang('change_password') => $url);
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