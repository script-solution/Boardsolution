<?php
/**
 * Contains the config-item-size class
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
 * The implementation of the config-item "size". Will lead to two int-controls to enter
 * width and height.
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_Size extends FWS_Config_Item_Default
{
	public function get_control($form)
	{
		$props = $this->_data->get_properties();
		list($x,$y) = explode('x',$this->_data->get_value());
		$str = $form->get_textbox($this->_data->get_name().'_x',$x,$props['size'],$props['maxlen']);
		$str .= ' X ';
		$str .= $form->get_textbox($this->_data->get_name().'_y',$y,$props['size'],$props['maxlen']);
		$str .= $this->get_suffix();
		return $str;
	}

	public function get_value()
	{
		$input = FWS_Props::get()->input();

		$x = $input->get_var($this->_data->get_name().'_x','post',FWS_Input::INTEGER);
		$y = $input->get_var($this->_data->get_name().'_y','post',FWS_Input::INTEGER);
		if(!$x)
			$x = 0;
		if(!$y)
			$y = 0;
		return $x.'x'.$y;
	}
}
?>