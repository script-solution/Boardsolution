<?php
/**
 * Contains the add-moderators-action
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
 * The add-moderators-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_moderators_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();
		$forums = FWS_Props::get()->forums();

		$new_mods = $input->get_var('user_add','post');
		$nodes = $forums->get_all_nodes();
		$mods = array();
		for($i = 0;$i < count($nodes);$i++)
		{
			$node = $nodes[$i];
			$data = $node->get_data();
			if($data->get_forum_type() == 'contains_threads')
				$mods[$data->get_id()] = FWS_Array_Utils::advanced_explode(',',$new_mods[$data->get_id()]);
		}

		if(!is_array($mods) || count($mods) == 0)
			return '';
		
		$count = 0;
		foreach($mods as $fid => $user_names)
		{
			FWS_Array_Utils::trim($user_names);
			foreach(BS_DAO::get_user()->get_users_by_names($user_names) as $data)
			{
				if(!BS_DAO::get_mods()->is_user_mod_in_forum($data['id'],$fid))
				{
					BS_DAO::get_mods()->create($fid,$data['id']);
					$count++;
				}
			}
		}

		if($count > 0)
		{
			$cache->refresh('moderators');
			$this->set_success_msg($locale->lang('add_moderators_success'));
			$this->set_action_performed(true);
		}

		return '';
	}
}
?>