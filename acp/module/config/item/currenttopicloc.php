<?php
/**
 * Contains the config-item-current-topic-loc class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The implementation of the config-item "current-topic-loc". Ensures that just either 'top'
 * or 'bottom' can be selected
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_CurrentTopicLoc extends PLIB_Config_Item_MultiEnum
{
	public function get_value()
	{
		$props = $this->_data->get_properties();
		$vals = $this->input->get_var($this->_data->get_name(),'post');
		if(!is_array($vals))
			$vals = array();
		
		if($props['type'] != 'combo')
			$vals = array_keys($vals);
		
		if(in_array('top',$vals))
		{
			$bottom = array_search('bottom',$vals);
			if($bottom !== false)
				unset($vals[$bottom]);
		}
		
		return implode(',',$vals);
	}
}
?>