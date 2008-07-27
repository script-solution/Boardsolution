<?php
/**
 * Contains the login-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The login-action
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_login extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$functions = PLIB_Props::get()->functions();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();

		if($user->is_loggedin())
			return 'You are already loggedin';

		if(!$input->isset_var('login','post'))
			return 'No form submitted?';

		// login
		$username = $input->get_var('user_login','post',PLIB_Input::STRING);
		$pw = $input->get_var('pw_login','post',PLIB_Input::STRING);
		$error_code = $user->login($username,$pw,true);
		
		// was the login successfull?
		if($user->is_loggedin())
		{
			$referer = $input->get_var('HTTP_REFERER','server',PLIB_Input::STRING);
			if($referer === null)
				$goto_url = $functions->get_start_url();
			else
			{
				$goto_url = $referer;

				// check if there is an action-parameter
				$matches = array();
				if(preg_match('/'.preg_quote(BS_URL_ACTION,'/').'=([a-zA-Z0-9_]+)/',$goto_url,$matches))
				{
					// does the module exist?
					$module_file = PLIB_Path::server_app().'front/module/'.$matches[1].'/module_';
					$module_file .= $matches[1].'.php';
					if(is_file($module_file))
					{
						// so include the module and check if it is a guest-only-module
						include_once($module_file);
						$class = 'BS_Front_Module_'.$matches[1];
	
						if(class_exists($class))
						{
							// instantiate the module
							$c = new $class();
	
							// if it is a guest-only module we don't want to redirect to that module
							if($c->is_guest_only())
								$goto_url = $functions->get_start_url();
						}
					}
				}
			}

			// so we want to show the status-page, if necessary
			$this->set_action_performed(true);
			$this->add_link($locale->lang('back'),$goto_url);
			$this->set_success_msg(sprintf(
				$locale->lang('success_'.BS_ACTION_LOGIN),
				$user->get_profile_val('user_name')
			));
			return '';
		}

		// otherwise we want to show nothing, therefore we simulate that we haven't done anything
		$this->set_action_performed(false);
		
		$this->set_redirect(true,$url->get_url('login','&amp;'.BS_URL_ID.'='.$error_code));
		return $locale->lang('login_error_'.$error_code);
	}
}
?>