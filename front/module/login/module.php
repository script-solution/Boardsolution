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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$user = FWS_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access(!$user->is_loggedin());

		$renderer->add_breadcrumb($locale->lang('login'),$url->get_url('login'));
	}
	
	/**
	 * @see BS_Front_Module::is_guest_only()
	 *
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
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$url = FWS_Props::get()->url();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();

		// max login-tries reached?
		if($input->isset_var('login','post') && $user->has_reached_max_login_tries())
		{
			$username = $input->get_var('user_login','post',FWS_Input::STRING);
			$pw = md5($input->get_var('pw_login','post',FWS_Input::STRING));
			
			$sec_code_field = FWS_StringHelper::generate_random_key(15);
			$user->set_session_data('sec_code_field',$sec_code_field);
			
			$tpl->add_variables(array(
				'max_login_tries' => true,
				'forum_index_url' => $url->get_url(0),
				'action_type' => BS_ACTION_LOGIN,
				'user' => $username,
				'sec_code_field' => $sec_code_field,
				'pw' => $pw,
				'security_code_img' => $url->get_url('security_code')
			));
		}
		// default login-form
		else if(!$user->is_loggedin())
			$functions->show_login_form(false);
	}
}
?>