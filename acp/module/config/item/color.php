<?php
/**
 * Contains the config-item-color class
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
 * The implementation of the config-item "spam". Will lead to a checkbox to enable/disable the
 * spam-protection and a combobox to configure the time.
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_Color extends FWS_Config_Item_Line
{
	/**
	 * Wether we've already added the color-picker
	 *
	 * @var boolean
	 */
	private static $_added_color_picker = false;
	
	public function get_control($form)
	{
		$locale = FWS_Props::get()->locale();

		$str = '#'.parent::get_control($form);
		
		// add color-picker javascript?
		if(!self::$_added_color_picker)
		{
			$file = FWS_Javascript::get_instance()->get_file('js/colorpicker.js','fws');
			$str .= '<script type="text/javascript" src="'.$file.'"></script>'."\n";
			self::$_added_color_picker = true;
		}
		
		// instantiate color-picker
		$id = $this->_data->get_id();
		$str .= '<script type="text/javascript">'."\n";
		$str .= '<!--'."\n";
		$str .= 'var cp_'.$id.' = new FWS_ColorPicker("'.FWS_Path::client_fw().'",';
		$str .= '"'.$this->_data->get_name().'");'."\n";
		$str .= '//-->'."\n";
		$str .= '</script>'."\n";
		
		// build image
		$str .= '&nbsp;<img id="cp_image_'.$id.'" src="acp/images/color_picker.gif" title="';
		$str .= $locale->lang('color_picker_hint').'"';
		$str .= ' alt="'.$locale->lang('color_picker_hint').'"';
		$str .= ' onmouseover="this.style.cursor = \'pointer\';"';
		$str .= ' onmouseout="this.style.cursor = \'default\';"';
		$str .= ' onclick="cp_'.$id.'.toggle(this.id,\'rt\')" />';
		return $str;
	}

	public function get_value()
	{
		$val = parent::get_value();
		if(!preg_match('/^[a-f0-9]{6}$/i',$val))
			return '';
		
		return $val;
	}
}
?>