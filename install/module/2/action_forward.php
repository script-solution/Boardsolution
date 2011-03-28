<?php
/**
 * Contains the forward-action of step 2
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
final class BS_Install_Action_2_forward extends BS_Install_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		if($input->isset_var('install_type','post'))
		{
			$type = $input->correct_var(
				'install_type','post',FWS_Input::STRING,array('full','update'),'full'
			);
			$user->set_session_data('install_type',$type);
		}

		$this->set_action_performed(true);
		if($input->isset_var('dir','get'))
			$this->set_redirect(true,$this->get_step_url());
		return '';
	}
}
?>