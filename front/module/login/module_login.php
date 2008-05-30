<?php
/**
 * Contains the login-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The login-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_login extends BS_Front_Module
{
	public function run()
	{
		// max login-tries reached?
		if($this->input->isset_var('login','post') && $this->user->has_reached_max_login_tries())
		{
			$user = $this->input->get_var('user_login','post',PLIB_Input::STRING);
			$pw = md5($this->input->get_var('pw_login','post',PLIB_Input::STRING));
			
			$sec_code_field = PLIB_StringHelper::generate_random_key(15);
			$this->user->set_session_data('sec_code_field',$sec_code_field);
			
			$this->tpl->add_variables(array(
				'max_login_tries' => true,
				'forum_index_url' => $this->url->get_url(0),
				'action_type' => BS_ACTION_LOGIN,
				'user' => $user,
				'sec_code_field' => $sec_code_field,
				'pw' => $pw,
				'security_code_img' => $this->url->get_standalone_url('front','security_code')
			));
		}
		// default login-form
		else if(!$this->user->is_loggedin())
			$this->functions->show_login_form(false);
	}
	
	public function get_location()
	{
		return array($this->locale->lang('login') => $this->url->get_url('login'));
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