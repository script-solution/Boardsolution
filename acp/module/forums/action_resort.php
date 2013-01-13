<?php
/**
 * Contains the resort-forums-action
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
 * The resort-forums-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_forums_resort extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$locale = FWS_Props::get()->locale();

		$this->_resort_forums(0);
		
		// refresh forums
		FWS_Props::get()->reload('forums');
		
		$this->set_success_msg($locale->lang('sort_successfully_corrected'));
		$this->set_action_performed(true);

		return '';
	}
	
	/**
	 * The recursive method to resort the forums
	 *
	 * @param int $parent_id the parent-id
	 */
	private function _resort_forums($parent_id)
	{
		$forums = FWS_Props::get()->forums();

		$sub_forums = $forums->get_direct_sub_nodes($parent_id);
		if($sub_forums != null)
		{
			$i = 1;
			foreach($sub_forums as $node)
			{
				/* @var $node FWS_Tree_Node */
				$data = $node->get_data();
				
				BS_DAO::get_forums()->update_sort($data->get_id(),$i);

				if($node->get_child_count() > 0)
					$this->_resort_forums($data->get_id());

				$i++;
			}
		}
	}
}
?>