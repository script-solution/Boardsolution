<?php
/**
 * Contains the edit-submodule for usergroups
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
 * The edit sub-module for the usergroups-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_usergroups_edit extends BS_ACP_SubModule
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
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_ADD_USER_GROUP,array('edit','add'));
		$renderer->add_action(BS_ACP_ACTION_EDIT_USER_GROUP,array('edit','edit'));

		$id = $input->get_var('id','get',FWS_Input::ID);
		$url = BS_URL::get_acpsub_url();
		if($id != null)
		{
			$url->set('id',$id);
			$renderer->add_breadcrumb($locale->lang('edit_group'),$url->to_url());
		}
		else
			$renderer->add_breadcrumb($locale->lang('insert_group'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		$id = $input->get_var('id','get',FWS_Input::ID);
		$type = $id != null ? 'edit' : 'add';
		
		$formurl = BS_URL::get_acpsub_url();

		if($type == 'edit')
		{
			$data = $cache->get_cache('user_groups')->get_element($id);
			$formurl->set('id',$id);
			$form_title = $locale->lang('edit_group');
			$action_type = BS_ACP_ACTION_EDIT_USER_GROUP;
		}
		else
		{
			$data = array(
				'group_title' => '',
				'group_color' => '',
				'group_rank_filled_image' => '',
				'group_rank_empty_image' => '',
				'overrides_mod' => 0,
				'is_visible' => 1,
				'is_team' => 0
			);
			foreach(BS_ACP_Module_UserGroups_Helper::get_permissions() as $name)
				$data[$name] = 0;
			
			$form_title = $locale->lang('insert_group');
			$action_type = BS_ACP_ACTION_ADD_USER_GROUP;
		}

		$this->request_formular();
		$is_guest_group = $id == BS_STATUS_GUEST;

		$tpl->add_variable_ref('default',$data);
		$tpl->add_variables(array(
			'form_target' => $formurl->to_url(),
			'action_type' => $action_type,
			'form_title' => $form_title,
			'is_guest_group' => $is_guest_group,
			'is_predefined' => $id == BS_STATUS_USER || $id == BS_STATUS_ADMIN
		));

		$fields = array();
		$guest_disallowed = BS_ACP_Module_UserGroups_Helper::get_guest_disallowed();
		foreach(BS_ACP_Module_UserGroups_Helper::get_permissions() as $name)
		{
			$fields[] = array(
				'name' => $name,
				'login_required' => $is_guest_group && in_array($name,$guest_disallowed),
				'value' => $data[$name],
				'show_description' => $locale->contains_lang('permission_'.$name.'_desc')
			);
		}

		$tpl->add_variable_ref('fields',$fields);
	}
}
?>