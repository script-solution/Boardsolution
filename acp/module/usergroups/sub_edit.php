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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
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
		if($id != null)
		{
			$renderer->add_breadcrumb(
				$locale->lang('edit_group'),
				BS_URL::get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
			);
		}
		else
		{
			$renderer->add_breadcrumb(
				$locale->lang('insert_group'),
				BS_URL::get_acpmod_url(0,'&amp;action=edit')
			);
		}
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

		$helper = BS_ACP_Module_UserGroups_Helper::get_instance();
		$id = $input->get_var('id','get',FWS_Input::ID);
		$type = $id != null ? 'edit' : 'add';

		if($type == 'edit')
		{
			$data = $cache->get_cache('user_groups')->get_element($id);
			$form_target = BS_URL::get_acpmod_url(0,'&amp;action=edit&amp;id='.$id);
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
			foreach($helper->get_permissions() as $name)
				$data[$name] = 0;
			
			$form_target = BS_URL::get_acpmod_url(0,'&amp;action=edit');
			$form_title = $locale->lang('insert_group');
			$action_type = BS_ACP_ACTION_ADD_USER_GROUP;
		}

		$this->request_formular();
		$is_guest_group = $id == BS_STATUS_GUEST;

		$tpl->add_array('default',$data);
		$tpl->add_variables(array(
			'color_picker_url' => BS_URL::get_url('color_picker','&amp;class=group_color&amp;raute=0'),
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
				'show_description' => $locale->contains_lang('permission_'.$name.'_desc')
			);
		}

		$tpl->add_array('fields',$fields);
	}
}
?>