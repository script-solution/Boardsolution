<?php
/**
 * Contains the config-item-color class
 *
 * @version			$Id: color.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The implementation of the config-item "spam". Will lead to a checkbox to enable/disable the
 * spam-protection and a combobox to configure the time.
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_Color extends PLIB_Config_Item_Line
{
	/**
	 * Wether we've already added the color-picker
	 *
	 * @var boolean
	 */
	private static $_added_color_picker = false;
	
	public function get_control($form)
	{
		$str = '#'.parent::get_control($form);
		
		// add color-picker javascript?
		if(!self::$_added_color_picker)
		{
			$file = PLIB_Javascript::get_instance()->get_file('js/colorpicker.js','lib');
			$str .= '<script type="text/javascript" src="'.$file.'"></script>'."\n";
			self::$_added_color_picker = true;
		}
		
		// instantiate color-picker
		$id = $this->_data->get_id();
		$str .= '<script type="text/javascript">'."\n";
		$str .= '<!--'."\n";
		$str .= 'var cp_'.$id.' = new PLIB_ColorPicker("'.PLIB_Path::lib().'",';
		$str .= '"'.$this->_data->get_name().'");'."\n";
		$str .= '//-->'."\n";
		$str .= '</script>'."\n";
		
		// build image
		$str .= '&nbsp;<img id="cp_image_'.$id.'" src="acp/images/color_picker.gif" title="';
		$str .= $this->locale->lang('color_picker_hint').'"';
		$str .= ' alt="'.$this->locale->lang('color_picker_hint').'"';
		$str .= ' onmouseover="this.style.cursor = \'pointer\';"';
		$str .= ' onmouseout="this.style.cursor = \'default\';"';
		$str .= ' onclick="cp_'.$id.'.toggle(this.id)" />';
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