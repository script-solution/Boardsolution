<?php
/**
 * Contains the login-module
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
 * The login-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
		
		$this->set_always_viewable(true);
		
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access(!$user->is_loggedin());

		$renderer->add_breadcrumb($locale->lang('login'),BS_URL::build_mod_url('login'));
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
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();

		$tpl->add_variables(array('loginform' => !$user->is_loggedin()));
		
		// max login-tries reached?
		if($input->isset_var('login','post') && $user->has_reached_max_login_tries())
		{
			$username = $input->get_var('user_login','post',FWS_Input::STRING);
			$pw = $input->get_var('pw_login','post',FWS_Input::STRING);
			
			$sec_code_field = FWS_StringHelper::generate_random_key(15);
			$user->set_session_data('sec_code_field',$sec_code_field);
			
			$tpl->add_variables(array(
				'max_login_tries' => true,
				'forum_index_url' => BS_URL::build_mod_url(),
				'action_type' => BS_ACTION_LOGIN,
				'user' => $username,
				'sec_code_field' => $sec_code_field,
				'pw' => $pw,
				'security_code_img' => BS_URL::build_standalone_url('security_code'),
				'loginform' => false
			));
		}
		// default login-form
		else if(!$user->is_loggedin())
			$functions->build_login_form(false);
	}
}
?>