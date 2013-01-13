<?php
/**
 * Contains the edit-submodule for additionalfields
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
 * The edit sub-module for the additionalfields-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_additionalfields_edit extends BS_ACP_SubModule
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
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_ADDFIELD,'edit');
		$renderer->add_action(BS_ACP_ACTION_ADD_ADDFIELD,'add');

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			$renderer->add_breadcrumb($locale->lang('add_new_field'),BS_URL::build_acpsub_url());
		else
		{
			$url = BS_URL::get_acpsub_url();
			$url->set('id',$id);
			$renderer->add_breadcrumb($locale->lang('edit_field'),$url->to_url());
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
		$id = $input->get_var('id','get',FWS_Input::ID);
		$type = $id != null ? 'edit' : 'add';
		
		if($type == 'edit')
		{
			$field = $cache->get_cache('user_fields')->get_element($id);
			if($field === null)
			{
				$this->report_error();
				return;
			}
			
			$default = BS_DAO::get_addfields()->get_by_id($id);
			$default['field_custom_display'] = htmlspecialchars($default['field_custom_display'],ENT_QUOTES);
		}
		else
		{
			$default = array(
				'field_name' => '',
				'display_name' => '',
				'field_type' => 'line',
				'field_length' => 0,
				'allowed_values' => '',
				'field_show_type' => 0,
				'field_validation' => '',
				'field_suffix' => '',
				'field_custom_display' => '',
				'field_is_required' => 0,
				'field_edit_notice' => '',
				'display_always' => 1,
			);
		}
		
		$form = $this->request_formular();
		$url = BS_URL::get_acpsub_url();
		
		if($type == 'edit')
		{
			$form_title = $locale->lang('edit_field');
			$url->set('id',$id);
			$url->set('at',BS_ACP_ACTION_EDIT_ADDFIELD);
			$murl = $url->to_url();
			$submit_title = $locale->lang('save');
		}
		else
		{
			$form_title = $locale->lang('add_new_field');
			$url->set('at',BS_ACP_ACTION_ADD_ADDFIELD);
			$murl = $url->to_url();
			$submit_title = $locale->lang('insert');
		}
		
		$types = array(
			'int' => $locale->lang('field_type_int'),
			'date' => $locale->lang('field_type_date'),
			'line' => $locale->lang('field_type_line'),
			'text' => $locale->lang('field_type_text'),
			'enum' => $locale->lang('field_type_enum')
		);
		$type_sel = array();
		foreach(array_keys($types) as $type)
			$type_sel[$type] = $form->get_radio_value('field_type',$type,$default['field_type'] == $type);
		
		$tpl->add_variable_ref('default',$default);
		$tpl->add_variables(array(
			'target_url' => $murl,
			'form_title' => $form_title,
			'submit_title' => $submit_title,
			'type_sel' => $type_sel,
			'types' => $types,
			'editable' => $default['field_name'] == '' || $default['field_name'] != 'birthday'
		));

		if($form->get_condition())
			$val = $this->_get_field_display_from_post();
		else
			$val = $default['field_show_type'];

		$locations = array();
		foreach(BS_ACP_Module_AdditionalFields_Helper::get_locations() as $loc)
		{
			$locations[] = array(
				'name' => $loc,
				'title' => $this->_get_location_name($loc).': ',
				'display' => ($val & $loc) != 0
			);
		}
		
		$tpl->add_variable_ref('locations',$locations);
	}

	/**
	 * determines the name of the given location
	 *
	 * @param int $loc the location
	 * @return string the name of the location
	 */
	private function _get_location_name($loc)
	{
		$locale = FWS_Props::get()->locale();

		switch($loc)
		{
			case BS_UF_LOC_POSTS:
				return $locale->lang('field_display_location_posts');
			case BS_UF_LOC_REGISTRATION:
				return $locale->lang('field_display_location_registration');
			case BS_UF_LOC_USER_DETAILS:
				return $locale->lang('field_display_location_user_details');
			case BS_UF_LOC_USER_PROFILE:
				return $locale->lang('field_display_location_user_profile');
			default:
				return '';
		}
	}

	/**
	 * generates the integer for the field-display-setting from the post-inputs
	 *
	 * @return int the integer-value
	 */
	private function _get_field_display_from_post()
	{
		$input = FWS_Props::get()->input();

		$result = 0;
		foreach(BS_ACP_Module_AdditionalFields_Helper::get_locations() as $loc)
		{
			$loc_val = $input->get_var('loc_'.$loc,'post',FWS_Input::INT_BOOL);
			if($loc_val == 1)
				$result |= $loc;
		}

		return $result;
	}
}
?>