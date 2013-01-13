<?php
/**
 * Contains the config-item-themes class
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
 * The implementation of the config-item "themes". Will display a combobox with all available
 * themes
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_Themes extends FWS_Config_Item_Default
{
	public function get_control($form)
	{
		$cache = FWS_Props::get()->cache();

		$themes = array();
		foreach($cache->get_cache('themes') as $data)
			$themes[$data['id']] = $data['theme_name'];
		
		$str = $form->get_combobox($this->_data->get_name(),$themes,$this->_data->get_value());
		$str .= $this->get_suffix();
		return $str;
	}

	public function get_value()
	{
		$input = FWS_Props::get()->input();

		return $input->get_var($this->_data->get_name(),'post',FWS_Input::ID);
	}
}
?>