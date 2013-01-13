<?php
/**
 * Contains the db-source-class for the additional-fields
 * 
 * @package			Boardsolution
 * @subpackage	src.addfield
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
 * The db-based implementation of the additional-fields-source
 * 
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_AddField_Source_DB extends FWS_Object implements FWS_AddField_Source
{
	public function get_fields()
	{
		$cache = FWS_Props::get()->cache();

		$fields = array();
		foreach($cache->get_cache('user_fields') as $data)
		{
			$fields[] = new BS_AddField_Data(
				$data['id'],$data['field_type'],$data['field_show_type'],$data['field_name'],
				$data['display_name'],$data['field_sort'],$data['field_is_required'],
				$data['field_edit_notice'],$data['field_suffix'],$data['display_always'],
				$data['field_length'],FWS_Array_Utils::advanced_explode("\n",$data['allowed_values']),
				$data['field_validation'],$data['field_custom_display']
			);
		}
		return $fields;
	}
	
	public function get_field($data)
	{
		switch($data->get_type())
		{
			case 'int':
				return new FWS_AddField_Type_Int($data);
			case 'date':
				return new FWS_AddField_Type_Date($data);
			case 'line':
				return new FWS_AddField_Type_Line($data);
			case 'text':
				return new FWS_AddField_Type_Text($data);
			case 'enum':
				return new FWS_AddField_Type_Enum($data);
			default:
				FWS_Helper::error('Unknown field-type "'.$data->get_type().'"!');
				return null;
		}
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>