<?php
/**
 * Contains the delete-index-action
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
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
 * The delete-index-action
 *
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Action_index_delete extends BS_DBA_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$db = FWS_Props::get()->db();
		$locale = FWS_Props::get()->locale();

		$stables = $input->get_var('tables','get',FWS_Input::STRING);
		$tables = FWS_Array_Utils::advanced_explode(';',$stables);
		if(count($tables) > 0)
			$db->execute('DROP TABLE `'.implode('`, `',$tables).'`');
		
		$this->set_success_msg(
			sprintf($locale->lang('delete_tables_success'),'"'.implode('", "',$tables).'"')
		);
		$this->set_action_performed(true);

		return '';
	}
}
?>