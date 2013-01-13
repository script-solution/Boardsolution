<?php
/**
 * Contains the send-pm-action
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
 * The send-pm-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_sendpm extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$auth = FWS_Props::get()->auth();
		$ips = FWS_Props::get()->ips();
		$locale = FWS_Props::get()->locale();
		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';

		// has the user the permission to write a PM?
		if(!$user->is_loggedin() || $user->get_profile_val('allow_pms') == 0)
			return 'You are a guest or you\'ve disabled PMs';

		// spam?
		$spam_pm_on = $auth->is_ipblock_enabled('spam_pm');
		if($spam_pm_on)
		{
			if($ips->entry_exists('pm'))
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
		$ips->add_entry('pm');

		$this->set_action_performed(true);
		$this->set_success_msg(sprintf(
			$locale->lang('success_'.BS_ACTION_SEND_PM),
			FWS_StringHelper::get_enum($pm->get_receiver_names())
		));
		$this->add_link(
			$locale->lang('go_to_inbox'),
			BS_URL::get_sub_url('userprofile','pminbox')
		);
		$this->add_link(
			$locale->lang('compose_another_pm'),
			BS_URL::get_sub_url('userprofile','pmcompose')
		);

		return '';
	}
}
?>