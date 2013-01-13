<?php
/**
 * Contains the client-submodule for acpaccess
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
 * The client sub-module for the acpaccess-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_acpaccess_client extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$cache = FWS_Props::get()->cache();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_ACPACCESS_GROUP,array('client','group'));
		$renderer->add_action(BS_ACP_ACTION_ACPACCESS_USER,array('client','user'));

		$type = $input->get_var('type','get',FWS_Input::STRING);
		$murl = BS_URL::get_acpsub_url(0,'client');
		$murl->set('type',$type);
		
		if($type == 'user')
		{
			$username = $this->_get_username();
			$renderer->add_breadcrumb(
				sprintf($locale->lang('permissions_for_user'),$username),
				$murl->set('name',$username)->to_url()
			);
		}
		else
		{
			$group = $this->_get_group();
			$gdata = $cache->get_cache('user_groups')->get_element($group);
			if($gdata !== null)
			{
				$renderer->add_breadcrumb(
					sprintf($locale->lang('permissions_for_group'),$gdata['group_title']),
					$murl->set('group',$group)->to_url()
				);
			}
		}
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();
		$cache = FWS_Props::get()->cache();
		$tpl = FWS_Props::get()->tpl();

		$type = $input->get_var('type','get',FWS_Input::STRING);
		$group = $this->_get_group();
		$username = $this->_get_username();
		
		if($type == 'user')
		{
			$data = BS_DAO::get_profile()->get_user_by_name($username);
			if($data === false || $auth->is_in_group($data['user_group'],BS_STATUS_ADMIN))
			{
				$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('user_not_found'));
				return;
			}

			$title = sprintf($locale->lang('permissions_for_user'),$username);
			$col_title = $locale->lang('current_module_permission');
			$atype = 'user';
			$aval = $data['id'];
			$usergroups = FWS_Array_Utils::advanced_explode(',',$data['user_group']);
			$action_type = BS_ACP_ACTION_ACPACCESS_USER;
		}
		else
		{
			$gdata = $cache->get_cache('user_groups')->get_element($group);
			if($gdata === null || $group == BS_STATUS_ADMIN || $group == BS_STATUS_GUEST)
			{
				$this->report_error();
				return;
			}

			$title = sprintf($locale->lang('permissions_for_group'),$gdata['group_title']);
			$col_title = $locale->lang('current_user_permissions');
			$atype = 'group';
			$aval = $group;
			$action_type = BS_ACP_ACTION_ACPACCESS_GROUP;
		}

		$tpl->add_variables(array(
			'action_type' => $action_type,
			'aval' => $aval,
			'user_group' => $group,
			'user_name' => $username,
			'type' => $type,
			'title' => $title,
			'current_permission_col_title' => $col_title
		));
		
		$this->request_formular();
		$acpaccess = $cache->get_cache('acp_access');

		// display modules
		$categories = array();
		foreach(BS_ACP_Menu::get_instance()->get_menu_items() as $group)
		{
			$categories[] = array(
				'name' => $locale->lang($group['title']),
				'mods' => array()
			);

			foreach($group['modules'] as $mod => $data)
			{
				// skip items that have not default access
				if(isset($data['access']) && $data['access'] != 'default')
					continue;
				
				// are the permissions?
				$has_direct_access = $acpaccess->element_exists_with(array(
					'module' => $mod,
					'access_type' => $atype,
					'access_value' => $aval
				));
				if($type == 'user')
				{
					$access = $has_direct_access;
					// he/she don't has direct access, so we check if any of the usergroups
					// the user belongs to has access
					if(!$access)
					{
						foreach($usergroups as $gid)
						{
							$check = $acpaccess->element_exists_with(array(
								'module' => $mod,
								'access_type' => 'group',
								'access_value' => $gid
							));
							if($check)
							{
								$access = true;
								break;
							}
						}
					}

					$has_access = BS_ACP_Utils::get_yesno($access);
				}
				else
					$has_access = BS_ACP_Utils::get_yesno($has_direct_access);

				$categories[count($categories) - 1]['mods'][] = array(
					'name' => $locale->lang($data['title']),
					'current_permission' => $has_access,
					'module' => $mod,
					'has_direct_access' => $has_direct_access
				);
			}
		}
		
		$tpl->add_variable_ref('categories',$categories);
	}
	
	/**
	 * Determines the username to use
	 *
	 * @return string the username
	 */
	private function _get_username()
	{
		$input = FWS_Props::get()->input();

		$username = $input->get_var('user_name','post',FWS_Input::STRING);
		if($username == null)
			$username = $input->get_var('name','get',FWS_Input::STRING);
		return $username;
	}
	
	/**
	 * Determines the group to use
	 *
	 * @return int the group-id
	 */
	private function _get_group()
	{
		$input = FWS_Props::get()->input();

		$group = $input->get_var('user_group','post',FWS_Input::ID);
		if($group == null)
			$group = $input->get_var('group','get',FWS_Input::ID);
		return $group;
	}
}
?>
