<?php
/**
 * Contains the add-additionalfields-action
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
 * The add-additionalfields-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_additionalfields_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$values = array();
		$result = BS_ACP_Module_AdditionalFields_Helper::retrieve_valid_field_attributes(0,'add',$values);
		if($result != '')
			return $result;

		BS_DAO::get_profile()->add_additional_fields(
			$values['field_name'],$values['field_type'],$values['field_length']
		);

		$values['field_sort'] = $cache->get_cache('user_fields')->get_element_count() + 1;
		BS_DAO::get_addfields()->create($values);
		$cache->refresh('user_fields');
		
		$this->set_success_msg($locale->lang('field_created_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>