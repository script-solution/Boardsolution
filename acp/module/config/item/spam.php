<?php
/**
 * Contains the config-item-spam class
 *
 * @version			$Id$
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
final class BS_ACP_Config_Item_Spam extends PLIB_Config_Item_Default
{
	/**
	 * The available times
	 *
	 * @var array
	 */
	private $_elements = array(
		5,10,15,20,25,30,45,60,120,180,300,900,1800,3600,7200,14400,43200,86400
	);
	
	public function get_control($form)
	{
		$name = $this->_data->get_name();
		$value = $this->_data->get_value();
		
		// add checkbox
		$str = $form->get_checkbox($name.'_enabled',$value != 0,'1',$this->locale->lang('enabled'));
		
		// add combo
		$options = array();
		$options[0] = ' - ';
		foreach($this->_elements as $e)
			$options[$e] = $e;
		$str .= '&nbsp;, '.$form->get_combobox($name.'_time',$options,$value);
		
		$str .= $this->_get_suffix();
		return $str;
	}

	public function get_value()
	{
		$name = $this->_data->get_name();
		if(!$this->input->isset_var($name.'_enabled','post'))
			return 0;
		
		return $this->input->correct_var($name.'_time','post',PLIB_Input::INTEGER,$this->_elements,3600);
	}
}
?>