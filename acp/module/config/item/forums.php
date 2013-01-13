<?php
/**
 * Contains the config-item-forums class
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
 * The implementation of the config-item "forums". Will display a multiple-combobox with all
 * forums.
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_Forums extends FWS_Config_Item_Default
{
	public function get_control($form)
	{
		$str = BS_ForumUtils::get_recursive_forum_combo(
			$this->_data->get_name().'[]',explode(',',$this->_data->get_value()),0,false,false
		);
		$str .= $this->get_suffix();
		return $str;
	}

	public function get_value()
	{
		$input = FWS_Props::get()->input();

		$value = $input->get_var($this->_data->get_name(),'post');
		if($value !== null)
			return implode(',',$value);
		return '';
	}
}
?>