<?php
/**
 * Contains the config-item-timezone class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The implementation of the config-item "timezone". Will display a combobox with all available
 * timezones
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_Timezone extends PLIB_Config_Item_Default
{
	public function get_control($form)
	{
		$str = $form->get_timezone_combo($this->_data->get_name(),$this->_data->get_value());
		$str .= $this->get_suffix();
		return $str;
	}

	public function get_value()
	{
		$input = PLIB_Props::get()->input();

		return $input->get_var($this->_data->get_name(),'post',PLIB_Input::STRING);
	}
}
?>