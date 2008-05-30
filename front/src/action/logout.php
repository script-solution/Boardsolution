<?php
/**
 * Contains the logout-action
 *
 * @version			$Id: logout.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The logout-action
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_logout extends BS_Front_Action_Base
{
	public function perform_action()
	{
		if(!$this->user->is_loggedin())
			return 'You are already logged out';

		// check if the session-id is valid
		if(!$this->functions->has_valid_get_sid())
			return 'Invalid session-id';

		$username = $this->user->get_user_name();

		$this->user->logout();

		$this->set_success_msg(sprintf($this->locale->lang('success_'.BS_ACTION_LOGOUT),$username));
		$this->set_redirect(true,$this->functions->get_start_url());
		$this->add_link($this->locale->lang('forumindex'),$this->functions->get_start_url());
		$this->set_action_performed(true);
		
		return '';
	}
}
?>