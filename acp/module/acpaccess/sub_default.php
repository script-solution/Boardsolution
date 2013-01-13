<?php
/**
 * Contains the default-submodule for acpaccess
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
 * The default sub-module for the acpaccess-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_acpaccess_default extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$options = BS_ACP_Module_ACPAccess_Helper::get_group_options();
		
		$this->request_formular();
		$tpl->add_variables(array(
			'groups' => $options,
			'action_param' => BS_URL_ACTION
		));

		// collect permissions
		$permissions = array();
		foreach(BS_DAO::get_acpaccess()->get_all() as $data)
		{
			// init the array, if not existing
			if(!isset($permissions[$data['module']]))
			{
				$permissions[$data['module']] = array(
					'user' => array(),
					'group' => array()
				);
			}

			if($data['access_type'] == 'user')
				$name = $data['user_name'];
			else
			{
				$gdata = $cache->get_cache('user_groups')->get_element($data['access_value']);
				$name = $gdata['group_title'];
			}

			// save the permission
			$permissions[$data['module']][$data['access_type']][] = $name;
		}

		// add modules
		$categories = array();
		foreach(BS_ACP_Menu::get_instance()->get_menu_items() as $group)
		{
			$categories[] = array(
				'name' => $locale->lang($group['title']),
				'mods' => array()
			);

			foreach($group['modules'] as $mod => $data)
			{
				// skip modules with no default access
				if(isset($data['access']) && $data['access'] != 'default')
					continue;
				
				// are the permissions?
				if(!isset($permissions[$mod]) || !is_array($permissions[$mod]))
				{
					$d_user = '-';
					$d_groups = '-';
				}
				else
				{
					$d_user = count($permissions[$mod]['user']) ? implode(', ',$permissions[$mod]['user']) : '-';
					$d_groups = count($permissions[$mod]['group']) ? implode(', ',$permissions[$mod]['group']) : '-';
				}

				$categories[count($categories) - 1]['mods'][] = array(
					'name' => $locale->lang($data['title']),
					'usernames' => $d_user,
					'groups' => $d_groups,
					'module' => $mod
				);
			}
		}
		
		$tpl->add_variable_ref('categories',$categories);
	}
}
?>