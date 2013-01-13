<?php
/**
 * Contains the choose-favorite-forums-action
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
 * The choose-favorite-forums-action
 *
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_userprofile_favforums extends BS_Front_Action_Base
{
	public function perform_action()
	{
		$user = FWS_Props::get()->user();
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$locale = FWS_Props::get()->locale();
		if(!$user->is_loggedin())
			return 'You are a guest';

		$ids = $input->get_var('favorite','post');
		if(!is_array($ids))
			$ids = array();
		
		// collect ids and invert them
		$fids = array();
		foreach(array_keys($ids) as $fid)
		{
			if(FWS_Helper::is_integer($fid))
				$fids[] = $fid;
		}
		$fids = $forums->get_nodes_with_other_ids($fids,false);
		$uid = $user->get_user_id();
		
		// delete the old ids
		BS_DAO::get_unreadhide()->delete_by_users(array($uid));
		
		// insert new ids
		BS_DAO::get_unreadhide()->create($uid,$fids);
		
		$this->set_action_performed(true);
		$this->add_link(
			$locale->lang('back'),BS_URL::get_sub_url('userprofile','favforums')
		);

		return '';
	}
}
?>