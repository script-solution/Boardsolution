<?php
/**
 * Contains the forward-action of step 4
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
final class BS_Install_Action_4_forward extends BS_Install_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		
		// transfer values to session
		$prefix = $input->get_var('table_prefix','post',FWS_Input::STRING);
		if($prefix !== null)
			$user->set_session_data('table_prefix',$prefix);
		
		if($input->get_var('dir','get',FWS_Input::STRING) != 'back')
		{
			$errors = BS_Install_Module_4_Helper::get_errors();
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