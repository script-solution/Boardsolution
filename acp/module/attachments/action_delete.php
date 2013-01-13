<?php
/**
 * Contains the delete-attachments-action
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
 * The delete-attachments-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_attachments_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$paths = $input->get_var('ids','get');
		if(!is_array($paths) || count($paths) == 0)
			return 'Got no paths via GET ("ids")';
		
		// ensure that the paths are correct
		foreach($paths as $k => $path)
			$paths[$k] = 'uploads/'.basename($path);
		
		// grab all valid ids from db
		$ids = array();
		foreach(BS_DAO::get_attachments()->get_by_paths($paths) as $data)
			$ids[] = $data['id'];
		
		// delete files
		foreach($paths as $path)
			@unlink(FWS_Path::server_app().$path);
		
		// delete db-attachments
		if(count($ids) > 0)
			BS_DAO::get_attachments()->delete_by_ids($ids);

		$this->set_success_msg($locale->lang('attachments_delete_successfull'));
		$this->set_action_performed(true);

		return '';
	}
}
?>