<?php
/**
 * Contains the delete-additionalfields-action
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
 * The delete-additionalfields-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_additionalfields_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$id_str = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		foreach($ids as $id)
		{
			$data = $cache->get_cache('user_fields')->get_element($id);
			if($data != null)
			{
				if($data['field_name'] == 'birthday')
					continue;
				
				BS_DAO::get_profile()->delete_additional_fields($data['field_name']);
				BS_DAO::get_addfields()->delete($id);
				BS_DAO::get_addfields()->dec_sort($data['field_sort']);
				
				// TODO: improve this!?
				$cache->refresh('user_fields');
			}
		}
		
		$this->set_success_msg($locale->lang('field_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>