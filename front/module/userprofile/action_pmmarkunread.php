<?php
/**
 * Contains the pm-mark-unread-action
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
 * The pm-mark-unread-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_pmmarkunread extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		// allowed to view pms?
		if(!$user->is_loggedin() || $cfg['enable_pms'] == 0 ||
				$user->get_profile_val('allow_pms') == 0)
			return 'You are a guest, PMs are disabled or you\'ve disabled PMs';

		// valid input?
		$delete = $input->get_var("delete","post");
		if($delete == null)
			$delete = FWS_Array_Utils::advanced_explode(',',$input->get_var(BS_URL_DEL,'get',FWS_Input::STRING));

		if(!FWS_Array_Utils::is_integer($delete) || count($delete) == 0)
			return 'error_no_checkbox_clicked';

		// update db
		BS_DAO::get_pms()->set_read_flag($delete,$user->get_user_id(),0);

		// finish
		$this->set_action_performed(true);
		$this->add_link($locale->lang('back'),BS_URL::get_sub_url('userprofile',0));

		return '';
	}
}
?>