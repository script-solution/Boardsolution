<?php
/**
 * Contains the update-userranks-action
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
 * The update-userranks-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_userranks_update extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$post_to = $input->get_var('post_to','post');
		$rank_name = $input->get_var('rank_name','post');

		$count = 0;
		if(FWS_Array_Utils::is_integer($post_to))
		{
			asort($post_to);

			$last_post_to = 0;
			foreach($post_to as $id => $post_to_val)
			{
				$data = $cache->get_cache('user_ranks')->get_element($id);
				if($data['rank'] != $rank_name[$id] || $data['post_from'] != $last_post_to ||
					$data['post_to'] != $post_to_val)
				{
					BS_DAO::get_ranks()->update_by_id($id,array(
						'rank' => $rank_name[$id],
						'post_from' => $last_post_to,
						'post_to' => $post_to_val
					));
					$count++;
				}

				$last_post_to = $post_to_val + 1;
			}

			if($count > 0)
				$cache->refresh('user_ranks');
		}

		$this->set_success_msg($locale->lang('user_ranks_updated_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>