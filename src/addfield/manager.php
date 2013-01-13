<?php
/**
 * Contains the additional-fields manager for boardsolution
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
 * The additional fields manager for Boardsolution
 *
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_AddField_Manager extends FWS_AddField_Manager
{
	/**
	 * @return BS_AddField_Manager the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(new BS_AddField_Source_DB());
	}
	
	/**
	 * Checks wether any required field is empty, that can be edited in the profile
	 *
	 * @return boolean true if so
	 */
	public function is_any_required_field_empty()
	{
		$user = FWS_Props::get()->user();

		foreach($this->get_fields_at(BS_UF_LOC_USER_PROFILE) as $field)
		{
			/* @var $field FWS_AddField_Field */
			$data = $field->get_data();
			$stored_val = $user->get_profile_val('add_'.$data->get_name());
			if($data->is_required() && $field->is_empty($stored_val))
				return true;
		}
		
		return false;
	}
}
?>