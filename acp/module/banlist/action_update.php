<?php
/**
 * Contains the update-bans-action
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
 * The update-bans-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_banlist_update extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();

		$types = $input->get_var('types','post');
		$values = $input->get_var('values','post');
		if(!is_array($types) || !is_array($values) || count($types) != count($values))
			return 'Invalid POST-variables "types", "values". No arrays? Size not equal?';
		
		$valid_types = array('mail','user','ip');
		foreach($values as $id => $value)
		{
			if(FWS_Helper::is_integer($id))
			{
				$type = in_array($types[$id],$valid_types) ? $types[$id] : 'ip';
				BS_DAO::get_bans()->update_by_id($id,$value,$type);
			}
		}

		$cache->refresh('banlist');
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>