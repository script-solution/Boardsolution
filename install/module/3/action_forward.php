<?php
/**
 * Contains the forward-action of step 3
 * 
 * @package			Boardsolution
 * @subpackage	install.module
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
 * The forward-action
 *
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Action_3_forward extends BS_Install_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		
		// transfer values to session
		if($input->isset_var('host','post'))
		{
			$vars = $input->get_vars(
				array('host','login','password','database','admin_login','admin_pw','admin_email','board_url'),
				'post',FWS_Input::STRING
			);
			foreach($vars as $name => $val)
				$user->set_session_data($name,$val);
		}
		
		if($input->get_var('dir','get',FWS_Input::STRING) != 'back')
		{
			$status = array();
			$values = BS_Install_Module_3_Helper::collect_vals();
			$errors = BS_Install_Module_3_Helper::check($values,$status);
			if(count($errors) > 0)
				return $errors;
		}
		
		$this->set_action_performed(true);
		if($input->isset_var('dir','get'))
			$this->set_redirect(true,$this->get_step_url());
		return '';
	}
}
?>