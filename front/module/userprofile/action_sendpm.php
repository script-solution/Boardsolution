<?php
/**
 * Contains the send-pm-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The send-pm-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_sendpm extends BS_Front_Action_Base
{
	public function perform_action()
	{
		// nothing to do?
		if(!$this->input->isset_var('submit','post'))
			return '';

		// has the user the permission to write a PM?
		if(!$this->user->is_loggedin() || $this->user->get_profile_val('allow_pms') == 0)
			return 'You are a guest or you\'ve disabled PMs';

		// spam?
		$spam_pm_on = $this->auth->is_ipblock_enabled('spam_pm');
		if($spam_pm_on)
		{
			if($this->ips->entry_exists('pm'))
				return 'ippmmsg';
		}
		
		// create and check plain-action
		$pm = BS_Front_Action_Plain_PM::get_default();
		$res = $pm->check_data();
		if($res != '')
			return $res;
		
		// perform action
		$pm->perform_action();

		// finish up
		$this->ips->add_entry('pm');

		$this->set_action_performed(true);
		$this->set_success_msg(sprintf(
			$this->locale->lang('success_'.BS_ACTION_SEND_PM),
			PLIB_Array_Utils::advanced_implode(',',$pm->get_receiver_names())
		));
		$this->add_link(
			$this->locale->lang('go_to_inbox'),
			$this->url->get_url(0,'&amp;'.BS_URL_LOC.'=pminbox')
		);
		$this->add_link(
			$this->locale->lang('compose_another_pm'),
			$this->url->get_url(0,'&amp;'.BS_URL_LOC.'=pmcompose')
		);

		return '';
	}
}
?>