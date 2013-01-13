<?php
/**
 * Contains the ugroups-submodule for user
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
 * The ugroups sub-module for the user-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_user_ugroups extends BS_ACP_SubModule
{
	/**
	 * The user-ids
	 *
	 * @var array
	 */
	private $_ids = null;
	
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
		$renderer = $doc->use_default_renderer();

		$this->_ids = $input->get_var('delete','post');
		if($this->_ids === null)
			$this->_ids = $input->get_var('ids','get',FWS_Input::STRING);
		
		if(!is_array($this->_ids))
			$this->_ids = FWS_Array_Utils::advanced_explode(',',$this->_ids);
		
		$renderer->add_action(BS_ACP_ACTION_USER_EDIT_UGROUPS,'ugroups');

		$url = BS_URL::get_acpsub_url();
		$url->set('ids',implode(',',$this->_ids));
		$renderer->add_breadcrumb($locale->lang('edit_groups'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cache = FWS_Props::get()->cache();
		$tpl = FWS_Props::get()->tpl();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();

		// invalid ids?
		if(count($this->_ids) == 0 || !FWS_Array_Utils::is_integer($this->_ids))
		{
			$this->report_error();
			return;
		}
		
		// collect groups
		$group_options = array();
		$maingroups = array();
		foreach($cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
			{
				if($gdata['is_visible'] == 1)
					$maingroups[$gdata['id']] = $gdata['group_title'];
				$group_options[$gdata['id']] = $gdata['group_title'];
			}
		}

		$tpl->add_variables(array(
			'user_ids' => implode(',',$this->_ids),
			'groups' => $group_options,
			'maingroups' => $maingroups,
			'action_type' => BS_ACP_ACTION_USER_EDIT_UGROUPS,
			'target_url' => BS_URL::build_acpsub_url()
		));
		
		$this->request_formular();

		// grab user from db
		$users = array();
		$userlist = BS_DAO::get_profile()->get_users_by_ids(
			$this->_ids,'u.`'.BS_EXPORT_USER_NAME.'`','ASC'
		);
		foreach($userlist as $data)
		{
			$groups = FWS_Array_Utils::advanced_explode(',',$data['user_group']);
			$current = $auth->get_usergroup_list($data['user_group'],false,true,true);

			$gdata = $cache->get_cache('user_groups')->get_element($groups[0]);
			$sel_groups = $groups;
			unset($sel_groups[0]);
			
			$users[] = array(
				'id' => $data['id'],
				'is_own_user' => $data['id'] == $user->get_user_id(),
				'user_name' => BS_ACP_Utils::get_userlink($data['id'],$data['user_name']),
				'current' => $current,
				'main_group' => $gdata['id'],
				'other_groups' => $sel_groups
			);
		}

		$tpl->add_variables(array(
			'user' => $users,
			'back_url' => BS_URL::build_acpmod_url()
		));
	}
}
?>