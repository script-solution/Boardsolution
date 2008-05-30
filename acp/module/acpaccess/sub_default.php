<?php
/**
 * Contains the default-submodule for acpaccess
 * 
 * @version			$Id: sub_default.php 713 2008-05-20 21:59:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	public function get_actions()
	{
		return array();
	}
	
	public function run()
	{
		$options = BS_ACP_Module_ACPAccess_Helper::get_instance()->get_group_options();
		
		$this->_request_formular(false,false);
		$this->tpl->add_variables(array(
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
				$gdata = $this->cache->get_cache('user_groups')->get_element($data['access_value']);
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
				'name' => $this->locale->lang($group['title']),
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
					'name' => $this->locale->lang($data['title']),
					'usernames' => $d_user,
					'groups' => $d_groups,
					'module' => $mod
				);
			}
		}
		
		$this->tpl->add_array('categories',$categories);
	}
	
	public function get_location()
	{
		return array();
	}
}
?>