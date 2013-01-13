<?php
/**
 * Contains the switch-smileys-action
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
 * The switch-smileys-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_smileys_switch extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();

		$ids = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($aids = FWS_StringHelper::get_ids($ids)) || count($aids) != 2)
			return 'Got an invalid id-string via GET (need 2 ids)';
		
		list($id1,$id2) = $aids;
		$data1 = BS_DAO::get_smileys()->get_by_id($id1);
		$data2 = BS_DAO::get_smileys()->get_by_id($id2);
		if($data1 === null || $data2 === null)
			return 'At least one of the ids "'.$id1.'","'.$id2.'" doesn\'t exist';
		
		BS_DAO::get_smileys()->update_sort($id1,false);
		BS_DAO::get_smileys()->update_sort($id2,true);
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>