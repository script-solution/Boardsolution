<?php
/**
 * Contains the helper-class for the additional-fields
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
 * An helper-class for the additional-fields-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_AdditionalFields_Helper extends FWS_UtilBase
{
	/**
	 * @return array an numeric array with all possible locations
	 */
	public static function get_locations()
	{
		return array(
			BS_UF_LOC_POSTS,BS_UF_LOC_REGISTRATION,BS_UF_LOC_USER_DETAILS,BS_UF_LOC_USER_PROFILE
		);
	}
	
	/**
	 * Collects the field-attributes from the POST-array and stores them in the given array
	 *
	 * @param int $id the field-id
	 * @param string $type 'edit' or 'add'
	 * @param array $values the result-array
	 * @return string the error-message if an error has occurred or an empty string
	 */
	public static function retrieve_valid_field_attributes($id,$type,&$values)
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$cache = FWS_Props::get()->cache();

		$manager  = BS_AddField_Manager::get_instance();
		$field = $id == 0 ? null : $manager->get_field($id);
		$locked = $id == 0 ? false : $field->get_data()->get_name() == 'birthday';
		
		$values['field_name'] = $input->get_var('field_name','post',FWS_Input::STRING);
		$values['display_name'] = $input->get_var('display_name','post',FWS_Input::STRING);
		$values['field_length'] = $input->get_var('field_length','post',FWS_Input::INTEGER);
		$values['field_type'] = $input->correct_var(
			'field_type','post',FWS_Input::STRING,array('int','line','text','date','enum'),'line'
		);
		$values['allowed_values'] = $input->get_var('field_values','post',FWS_Input::STRING);
		$values['field_suffix'] = $input->get_var('field_suffix','post',FWS_Input::STRING);
		$values['field_custom_display'] = FWS_StringHelper::htmlspecialchars_back(
			$input->get_var('field_custom_display','post',FWS_Input::STRING)
		);
		$values['field_validation'] = $input->get_var(
			'field_validation','post',FWS_Input::STRING
		);
		$values['field_is_required'] = $input->get_var(
			'field_is_required','post',FWS_Input::INT_BOOL
		);
		$values['field_edit_notice'] = $input->get_var(
			'field_edit_notice','post',FWS_Input::STRING
		);
		$values['display_always'] = $input->get_var(
			'display_always','post',FWS_Input::INT_BOOL
		);

		if(!$locked && !preg_match('/^[a-z0-9_]+$/i',$values['field_name']))
			return $locale->lang('field_name_invalid');

		if($type == 'add')
		{
			if($cache->get_cache('user_fields')->element_exists_with(
					array('field_name' => $values['field_name'])))
				return $locale->lang('field_name_exists');
		}

		if(trim($values['display_name']) == '')
			return $locale->lang('display_name_empty');

		if(!$locked && ($values['field_type'] == 'int' ||
			$values['field_type'] == 'line') && ($values['field_length'] == null ||
				$values['field_length'] <= 0 || $values['field_length'] > 255))
			return $locale->lang('field_length_invalid');

		if($values['field_type'] == 'enum')
		{
			$lines = array();
			$values['field_length'] = 0;
			$input_lines = explode("\n",$values['allowed_values']);
			for($i = 0;$i < count($input_lines);$i++)
			{
				if(trim($input_lines[$i]) != '')
					$lines[] = trim($input_lines[$i]);
			}

			if(count($lines) < 2)
				return $locale->lang('field_values_invalid');
			
			$values['allowed_values'] = implode("\n",$lines);
		}
		else if($values['field_type'] == 'text')
			$values['field_length'] = 0;

		$values['field_show_type'] = 0;
		foreach(self::get_locations() as $loc)
		{
			if($input->get_var('loc_'.$loc,'post',FWS_Input::INT_BOOL) == 1)
				$values['field_show_type'] |= $loc;
		}
		
		// ensure that this fields will not be updated
		if($locked)
		{
			unset($values['field_name']);
			unset($values['field_type']);
			unset($values['field_length']);
			unset($values['allowed_values']);
		}

		return '';
	}
}
?>