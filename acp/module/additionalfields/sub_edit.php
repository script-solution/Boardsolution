<?php
/**
 * Contains the edit-submodule for additionalfields
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_ADDFIELD,'edit');
		$renderer->add_action(BS_ACP_ACTION_ADD_ADDFIELD,'add');

		$id = $input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$renderer->add_breadcrumb(
				$locale->lang('add_new_field'),
				$url->get_acpmod_url(0,'&amp;action=add')
			);
		}
		else
		{
			$renderer->add_breadcrumb(
				$locale->lang('edit_field'),
				$url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
			);
		}
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		$helper = BS_ACP_Module_AdditionalFields_Helper::get_instance();
		$id = $input->get_var('id','get',PLIB_Input::ID);
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

		if($type == 'edit')
		{
			$form_title = $locale->lang('edit_field');
			$murl = $url->get_acpmod_url(
				0,'&amp;action=edit&amp;id='.$id.'&amp;at='.BS_ACP_ACTION_EDIT_ADDFIELD
			);
			$submit_title = $locale->lang('save');
		}
		else
		{
			$form_title = $locale->lang('add_new_field');
			$murl = $url->get_acpmod_url(0,'&amp;action=edit&amp;at='.BS_ACP_ACTION_ADD_ADDFIELD);
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
		
		$tpl->add_array('default',$default);
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
		foreach($helper->get_locations() as $loc)
		{
			$locations[] = array(
				'name' => $loc,
				'title' => $this->_get_location_name($loc).': ',
				'display' => ($val & $loc) != 0
			);
		}
		
		$tpl->add_array('locations',$locations);
	}

	/**
	 * determines the name of the given location
	 *
	 * @param int $loc the location
	 * @return string the name of the location
	 */
	private function _get_location_name($loc)
	{
		$locale = PLIB_Props::get()->locale();

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
		$input = PLIB_Props::get()->input();

		$helper = BS_ACP_Module_AdditionalFields_Helper::get_instance();
		$result = 0;
		foreach($helper->get_locations() as $loc)
		{
			$loc_val = $input->get_var('loc_'.$loc,'post',PLIB_Input::INT_BOOL);
			if($loc_val == 1)
				$result |= $loc;
		}

		return $result;
	}
}
?>