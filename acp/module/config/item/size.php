<?php
/**
 * Contains the config-item-size class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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