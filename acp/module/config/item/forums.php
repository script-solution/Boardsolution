<?php
/**
 * Contains the config-item-forums class
 *
 * @version			$Id: forums.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The implementation of the config-item "forums". Will display a multiple-combobox with all
 * forums.
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_Forums extends PLIB_Config_Item_Default
{
	public function get_control($form)
	{
		$str = BS_ForumUtils::get_instance()->get_recursive_forum_combo(
			$this->_data->get_name().'[]',explode(',',$this->_data->get_value()),0,false,false
		);
		$str .= $this->_get_suffix();
		return $str;
	}

	public function get_value()
	{
		return implode(',',$this->input->get_var($this->_data->get_name(),'post'));
	}
}
?>