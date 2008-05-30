<?php
/**
 * Contains the edit-submodule for usergroups
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_ADD_USER_GROUP => array('edit','add'),
			BS_ACP_ACTION_EDIT_USER_GROUP => array('edit','edit')
		);
	}
	
	public function run()
	{
		$helper = BS_ACP_Module_UserGroups_Helper::get_instance();
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		$type = $id != null ? 'edit' : 'add';

		if($type == 'edit')
		{
			$data = $this->cache->get_cache('user_groups')->get_element($id);
			$form_target = $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id);
			$form_title = $this->locale->lang('edit_group');
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
				'is_visible' => 1
			);
			foreach($helper->get_permissions() as $name)
				$data[$name] = 0;
			
			$form_target = $this->url->get_acpmod_url(0,'&amp;action=edit');
			$form_title = $this->locale->lang('insert_group');
			$action_type = BS_ACP_ACTION_ADD_USER_GROUP;
		}

		$this->_request_formular();
		$is_guest_group = $id == BS_STATUS_GUEST;

		$this->tpl->add_array('default',$data);
		$this->tpl->add_variables(array(
			'color_picker_url' => $this->url->get_standalone_url(
				'acp','color_picker','&amp;class=group_color&amp;raute=0'
			),
			'form_target' => $form_target,
			'action_type' => $action_type,
			'form_title' => $form_title,
			'is_guest_group' => $is_guest_group,
			'is_predefined' => $id == BS_STATUS_USER || $id == BS_STATUS_ADMIN
		));

		$fields = array();
		$guest_disallowed = $helper->get_guest_disallowed();
		foreach($helper->get_permissions() as $name)
		{
			$fields[] = array(
				'name' => $name,
				'login_required' => $is_guest_group && in_array($name,$guest_disallowed),
				'value' => $data[$name],
				'show_description' => $this->locale->contains_lang('permission_'.$name.'_desc')
			);
		}

		$this->tpl->add_array('fields',$fields);
	}
	
	public function get_location()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id != null)
		{
			return array(
				$this->locale->lang('edit_group') => $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
			);
		}
		
		return array(
			$this->locale->lang('insert_group') => $this->url->get_acpmod_url(0,'&amp;action=edit')
		);
	}
}
?>