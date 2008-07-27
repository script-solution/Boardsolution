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
		$user = PLIB_Props::get()->user();
		$functions = PLIB_Props::get()->functions();
		$locale = PLIB_Props::get()->locale();

		$username = $user->get_user_name();

		// don't report an error here because this may happen if the session-id doesn't exist anymore
		// and therefore the user is already logged out
		if($user->is_loggedin())
		{
			// check if the session-id is valid
			if(!$functions->has_valid_get_sid())
				return 'Invalid session-id';
	
			$user->logout();
		}
		
		$this->set_success_msg(sprintf($locale->lang('success_'.BS_ACTION_LOGOUT),$username));
		$this->set_redirect(true,$functions->get_start_url());
		$this->add_link($locale->lang('forumindex'),$functions->get_start_url());
		$this->set_action_performed(true);
		
		return '';
	}
}
?>