<?php
/**
 * Contains the register-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The register-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_register_default extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		$ips = PLIB_Props::get()->ips();
		$functions = PLIB_Props::get()->functions();
		$locale = PLIB_Props::get()->locale();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// the user has to be a guest
		if($user->is_loggedin())
			return 'You are already loggedin';
		
		if($cfg['enable_registrations'] == 0)
			return 'Registrations are disabled';

		// check if the user already registered
		$spam_reg_on = $auth->is_ipblock_enabled('spam_reg');
		if($spam_reg_on)
		{
			if($ips->entry_exists('reg'))
				return 'registeripsperre';
		}

		if(!$functions->check_security_code())
			return 'invalid_security_code';

		// has the user agreed to the terms?
		if(!$input->isset_var('agree_to_terms','post'))
			return 'register_user_agreement';

		// build plain-action and check for errors
		$register = BS_Front_Action_Plain_Register::get_default();
		if(is_string($register))
			return $register;
		
		$res = $register->check_data();
		if($res != '')
			return $res;
		
		// perform action
		$register->perform_action();
		
		// finish up
		$this->set_action_performed(true);
		$this->add_link($locale->lang('forumindex'),$functions->get_start_url());
		$this->set_success_msg(
			$locale->lang('success_'.BS_ACTION_REGISTER.'_'.$cfg['account_activation'])
		);

		return '';
	}
}
?>