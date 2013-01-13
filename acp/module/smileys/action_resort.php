<?php
/**
 * Contains the resort-smileys-action
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
 * The resort-smileys-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_smileys_resort extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$locale = FWS_Props::get()->locale();

		$i = 1;
		foreach(BS_DAO::get_smileys()->get_list() as $smiley)
		{
			$fields = array(
				'sort_key' => $i++
			);
			BS_DAO::get_smileys()->update_by_id($smiley['id'],$fields);
		}
		
		$this->set_success_msg($locale->lang('sort_successfully_corrected'));
		$this->set_action_performed(true);

		return '';
	}
}
?>