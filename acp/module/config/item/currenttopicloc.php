<?php
/**
 * Contains the config-item-current-topic-loc class
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
 * The implementation of the config-item "current-topic-loc". Ensures that just either 'top'
 * or 'bottom' can be selected
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Config_Item_CurrentTopicLoc extends FWS_Config_Item_MultiEnum
{
	public function get_value()
	{
		$input = FWS_Props::get()->input();

		$props = $this->_data->get_properties();
		$vals = $input->get_var($this->_data->get_name(),'post');
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