<?php
/**
 * Contains the ugroups-user-action
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
 * The ugroups-user-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_user_ugroups extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();

		$main_group = $input->get_var('main_group','post');
		$other_groups = $input->get_var('other_groups','post');
		$idstr = $input->get_var('delete','post',FWS_Input::STRING);
		$ids = FWS_Array_Utils::advanced_explode(',',$idstr);
		
		if(!is_array($ids) || count($ids) == 0)
			return 'No valid ids got via POST';
		
		// add the main-group to the users
		$users = array();
		foreach($ids as $uid)
		{
			if(!FWS_Helper::is_integer($uid))
				return 'The id "'.$uid.'" is invalid';
			
			$uid = (int)$uid;
			if($uid == $user->get_user_id())
				$users[$uid] = array($user->get_user_group());
			else
			{
				$gdata = $cache->get_cache('user_groups')->get_element($main_group[$uid]);
				if($gdata === null)
					return 'The group "'.$main_group[$uid].'" doesn\'t exist!';
				if($gdata['is_visible'] == 0)
					return 'You can\'t choose invisible groups as main-group!';
				
				$users[$uid] = array($main_group[$uid]);
			}
		}

		// add the other groups
		if(is_array($other_groups))
		{
			foreach($other_groups as $uid => $groups)
			{
				$uid = (int)$uid;
				foreach($groups as $gid)
				{
					if($cache->get_cache('user_groups')->key_exists($gid))
						$users[$uid][] = $gid;
				}
			}
		}

		// now update the groups
		$count = 0;
		foreach($users as $id => $groups)
		{
			$groups = array_unique($groups);
			BS_DAO::get_profile()->update_user_by_id(array('user_group' => implode(',',$groups).','),$id);
			$count++;
		}

		$this->set_success_msg($locale->lang('user_groups_edited_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>