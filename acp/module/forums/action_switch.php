<?php
/**
 * Contains the switch-forums-action
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
 * The switch-forums-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_forums_switch extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();

		$id_str = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)) || count($ids) != 2)
			return 'Got an invalid id-string via GET';
		
		// check if the forums exist and have the same parent
		list($fid1,$fid2) = $ids;
		$data1 = $forums->get_node_data($fid1);
		$data2 = $forums->get_node_data($fid2);
		if($data1 === null || $data2 === null || $data1->get_parent_id() != $data2->get_parent_id())
			return 'Forums with ids "'.$fid1.'","'.$fid2.'" don\'t exist or are not in the same parent-forum';
		
		// update sort
		BS_DAO::get_forums()->update_sort($fid1,array('sortierung - 1'));
		BS_DAO::get_forums()->update_sort($fid2,array('sortierung + 1'));
		
		// refresh forums
		FWS_Props::get()->reload('forums');
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>