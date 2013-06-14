<?php
/**
 * Contains the default-submodule for additionalfields
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
 * The default sub-module for the additionalfields-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_additionalfields_default extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_DELETE_ADDFIELDS,'delete');
		$renderer->add_action(BS_ACP_ACTION_SWITCH_ADDFIELDS,'switch');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();

		if(($delete = $input->get_var('delete','post')) != null)
		{
			$rows = $cache->get_cache('user_fields')->get_field_vals_of_keys($delete,'display_name');
			$namelist = FWS_StringHelper::get_enum($rows,$locale->lang('and'));
			
			$url = BS_URL::get_acpsub_url();
			$url->set('at',BS_ACP_ACTION_DELETE_ADDFIELDS);
			$url->set('ids',implode(',',$delete));
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->to_url(),
				BS_URL::build_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		$fields = $cache->get_cache('user_fields');
		
		$sort_options = array();
		$num = $fields->get_element_count();
		for($i = 1;$i <= $num;$i++)
			$sort_options[$i] = $i;
		
		$tpl->add_variable_ref('sort_options',$sort_options);
		$this->request_formular();
		
		$url = BS_URL::get_acpsub_url();
		$url->set('at',BS_ACP_ACTION_SWITCH_ADDFIELDS);
		
		$i = 0;
		$tplfields = array();
		$elements = array_values($fields->get_elements());
		foreach($this->_filter_fields($elements,$search) as $data)
		{
			$field_type = $this->_get_field_type_name($data['field_type']);
			if($data['field_type'] != 'enum' && $data['field_type'] != 'text' && $data['field_type'] != 'date')
				$field_type .= ' ('.$data['field_length'].')';
			$display = $this->_get_display_locations_images($data['field_show_type']);
			
			if($i > 0)
			{
				$up_ids = $elements[$i - 1]['id'].','.$data['id'];
				$switch_up_url = $url->set('ids',$up_ids)->to_url();
			}
			else
				$switch_up_url = '';

			if($i < count($elements) - 1)
			{
				$down_ids = $elements[$i + 1]['id'].','.$data['id'];
				$switch_down_url = $url->set('ids',$down_ids)->to_url();
			}
			else
				$switch_down_url = '';
			
			$tplfields[] = array(
				'id' => $data['id'],
				'name' => $data['field_name'],
				'title' => $data['display_name'],
				'is_required' => $data['field_is_required'],
				'deletable' => $data['field_name'] != 'birthday',
				'type' => $field_type,
				'sort' => $data['field_sort'],
				'display' => $display,
				'switch_up_url' => $switch_up_url,
				'switch_down_url' => $switch_down_url
			);
			$i++;
		}
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variable_ref('fields',$tplfields);
		$tpl->add_variables(array(
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => stripslashes($search)
		));
	}
	
	/**
	 * Filters the given fields for the given keyword
	 *
	 * @param array $fields all fields
	 * @param string $keyword the keyword
	 * @return array the result-fields
	 */
	private function _filter_fields($fields,$keyword)
	{
		if(!$keyword)
			return $fields;
		
		$res = array();
		foreach($fields as $field)
		{
			if(stripos($field['field_name'],$keyword) !== false)
				$res[] = $field;
			else if(stripos($field['field_type'],$keyword) !== false)
				$res[] = $field;
			else if(stripos($field['display_name'],$keyword) !== false)
				$res[] = $field;
		}
		return $res;
	}

	/**
	 * determines the name of the field-type
	 *
	 * @param string $type the field-type
	 * @return string the name of the given type
	 */
	private function _get_field_type_name($type)
	{
		$locale = FWS_Props::get()->locale();

		switch($type)
		{
			case 'date':
				return $locale->lang('field_type_date');
			
			case 'int':
				return $locale->lang('field_type_int');

			case 'line':
				return $locale->lang('field_type_line');

			case 'text':
				return $locale->lang('field_type_text');

			default:
				return $locale->lang('field_type_enum');
		}
	}

	/**
	 * Collects all locations
	 *
	 * @param int $display the location-field
	 * @return array an numeric array of the form: <code>array(<index> => <image>)</code>
	 */
	private function _get_display_locations_images($display)
	{
		$result = array();
		foreach(BS_ACP_Module_AdditionalFields_Helper::get_locations() as $loc)
		{
			if(($display & $loc) != 0)
				$result[] = '<img src="'.FWS_Path::client_app().'acp/images/ok.gif" alt="ok" />';
			else
				$result[] = '<img src="'.FWS_Path::client_app().'acp/images/failed.gif" alt="failed" />';
		}

		return $result;
	}
}
?>