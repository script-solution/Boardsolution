<?php
/**
 * Contains the switch-addfields-action
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
 * The switch-addfields-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_additionalfields_switch extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();

		$id_str = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)) || count($ids) != 2)
			return 'Got an invalid id-string via GET';
		
		list($id1,$id2) = $ids;
		$first = BS_DAO::get_addfields()->get_by_id($id1);
		$second = BS_DAO::get_addfields()->get_by_id($id2);
		if(!$first['field_sort'])
			return 'A field with id "'.$id1.'" does not exist';
		if(!$second['field_sort'])
			return 'A field with id "'.$id2.'" does not exist';
		
		// update sort
		BS_DAO::get_addfields()->update_sort_by_id($second['field_sort'],$id1);
		BS_DAO::get_addfields()->update_sort_by_id($first['field_sort'],$id2);
		
		// refresh cache
		$cache->refresh('user_fields');
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>