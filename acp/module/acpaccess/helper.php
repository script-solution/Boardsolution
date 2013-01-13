<?php
/**
 * Contains the helper-class for the module ACPAccess
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
 * An helper-class for the module ACPAccess of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_ACPAccess_Helper extends FWS_UtilBase
{
	/**
	 * builds the options for the group-combobox
	 *
	 * @return array an associative array with the user-groups (except admins and guests)
	 */
	public static function get_group_options()
	{
		$cache = FWS_Props::get()->cache();

		$options = array();
		foreach($cache->get_cache('user_groups') as $row)
		{
			if($row['id'] != BS_STATUS_GUEST && $row['id'] != BS_STATUS_ADMIN)
				$options[$row['id']] = $row['group_title'];
		}
		return $options;
	}

	/**
	 * determines the name of the given module
	 *
	 * @param string $module the module
	 * @return string the name of the module (the LANG-entry)
	 */
	public static function get_module_name($module)
	{
		foreach(BS_ACP_Menu::get_instance()->get_menu_items() as $group)
		{
			foreach($group['modules'] as $mod => $data)
			{
				if($mod == $module)
					return $data['title'];
			}
		}

		return '';
	}
}
?>