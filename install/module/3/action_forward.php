<?php
/**
 * Contains the forward-action of step 3
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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