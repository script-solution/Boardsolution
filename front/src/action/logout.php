<?php
/**
 * Contains the logout-action
 *
 * @version			$Id$
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
		$username = $this->user->get_user_name();

		// don't report an error here because this may happen if the session-id doesn't exist anymore
		// and therefore the user is already logged out
		if($this->user->is_loggedin())
		{
			// check if the session-id is valid
			if(!$this->functions->has_valid_get_sid())
				return 'Invalid session-id';
	
			$this->user->logout();
		}
		
		$this->set_success_msg(sprintf($this->locale->lang('success_'.BS_ACTION_LOGOUT),$username));
		$this->set_redirect(true,$this->functions->get_start_url());
		$this->add_link($this->locale->lang('forumindex'),$this->functions->get_start_url());
		$this->set_action_performed(true);
		
		return '';
	}
}
?>