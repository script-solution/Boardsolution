<?php
/**
 * Contains the config-item-themes class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The implementation of the config-item "themes". Will display a combobox with all available
 * themes
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_Themes extends PLIB_Config_Item_Default
{
	public function get_control($form)
	{
		$themes = array();
		foreach($this->cache->get_cache('themes') as $data)
			$themes[$data['id']] = $data['theme_name'];
		
		$str = $form->get_combobox($this->_data->get_name(),$themes,$this->_data->get_value());
		$str .= $this->_get_suffix();
		return $str;
	}

	public function get_value()
	{
		return $this->input->get_var($this->_data->get_name(),'post',PLIB_Input::ID);
	}
}
?>