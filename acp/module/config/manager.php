<?php
/**
 * Contains the config-manager for BS
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
 * The item-manager for the ACP of BS which supports additional item-types
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Config_Manager extends FWS_Config_Manager
{
	protected function get_item($data)
	{
		$item = parent::get_item($data);
		if($item !== null)
			return $item;
		
		$type = $data->get_type();
		$file = FWS_Path::server_app().'acp/module/config/item/'.$type.'.php';
		if(is_file($file))
		{
			include_once($file);
			$class = 'BS_ACP_Config_Item_'.$type;
			if(class_exists($class))
				return new $class($data);
		}
		
		return null;
	}
}
?>