<?php
/**
 * Contains the config-item-languages class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The implementation of the config-item "languages". Will display a combobox with all available
 * languages
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_Languages extends PLIB_Config_Item_Default
{
	public function get_control($form)
	{
		$cache = PLIB_Props::get()->cache();

		$langs = array();
		foreach($cache->get_cache('languages') as $data)
			$langs[$data['id']] = $data['lang_name'];
		
		$str = $form->get_combobox($this->_data->get_name(),$langs,$this->_data->get_value());
		$str .= $this->get_suffix();
		return $str;
	}

	public function get_value()
	{
		$input = PLIB_Props::get()->input();

		return $input->get_var($this->_data->get_name(),'post',PLIB_Input::ID);
	}
}
?>