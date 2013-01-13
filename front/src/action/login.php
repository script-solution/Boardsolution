<?php
/**
 * Contains the login-action
 * 
 * @package			Boardsolution
 * @subpackage	front.src.action
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
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();

		if($user->is_loggedin())
			return 'You are already loggedin';

		if(!$input->isset_var('login','post'))
			return 'No form submitted?';

		// login
		$username = $input->get_var('user_login','post',FWS_Input::STRING);
		$pw = $input->get_var('pw_login','post',FWS_Input::STRING);
		$error_code = $user->login($username,$pw,true);
		
		// was the login successfull?
		if($user->is_loggedin())
		{
			$referer = $input->get_var('HTTP_REFERER','server',FWS_Input::STRING);
			if($referer === null)
				$goto_url = BS_URL::get_start_url();
			else
			{
				$goto_url = $referer;

				// check if there is an action-parameter
				$matches = array();
				if(preg_match('/'.preg_quote(BS_URL_ACTION,'/').'=([a-zA-Z0-9_]+)/',$goto_url,$matches))
				{
					// does the module exist?
					$module_file = FWS_Path::server_app().'front/module/'.$matches[1].'/module.php';
					if(is_file($module_file))
					{
						// so include the module and check if it is a guest-only-module
						include_once($module_file);
						$class = 'BS_Front_Module_'.$matches[1];
	
						if(class_exists($class))
						{
							$doc = FWS_Props::get()->doc();
							
							// instantiate the module
							$c = new $class();
							
							// if it is a guest-only module we don't want to redirect to that module
							if($c->is_guest_only())
								$goto_url = BS_URL::get_start_url();
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

		$this->set_action_performed(false);
		return $locale->lang('login_error_'.$error_code);
	}
}
?>