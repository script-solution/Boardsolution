<?php
/**
 * Contains the default-submodule for additionalfields
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * @param BS_ACP_Page $doc
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
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				BS_URL::get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_ADDFIELDS.'&amp;ids='.implode(',',$delete)
				),
				BS_URL::get_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		$fields = $cache->get_cache('user_fields');
		
		$sort_options = array();
		$num = $fields->get_element_count();
		for($i = 1;$i <= $num;$i++)
			$sort_options[$i] = $i;
		
		$tpl->add_array('sort_options',$sort_options);
		$this->request_formular();
		
		$i = 0;
		$tplfields = array();
		$elements = array_values($fields->get_elements());
		foreach($this->_filter_fields($elements,$search) as $data)
		{
			$field_type = $this->_get_field_type_name($data['field_type']);
			if($data['field_type'] != 'enum' && $data['field_type'] != 'text' && $data['field_type'] != 'date')
				$field_type .= ' ( '.$data['field_length'].' )';
			$display = $this->_get_display_locations_images($data['field_show_type']);
			
			if($i > 0)
			{
				$up_ids = $elements[$i - 1]['id'].','.$data['id'];
				$switch_up_url = BS_URL::get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_SWITCH_ADDFIELDS.'&amp;ids='.$up_ids
				);
			}
			else
				$switch_up_url = '';

			if($i < count($elements) - 1)
			{
				$down_ids = $elements[$i + 1]['id'].','.$data['id'];
				$switch_down_url = BS_URL::get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_SWITCH_ADDFIELDS.'&amp;ids='.$down_ids
				);
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
		$tpl->add_array('fields',$tplfields);
		$tpl->add_variables(array(
			'search_url' => $input->get_var('PHP_SELF','server',FWS_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
	}
	
	/**
	 * Filters the given fields for the given keyword
	 *
	 * @param array $fields all fields
	 * @param string $keyword the keyword
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
		$helper = BS_ACP_Module_AdditionalFields_Helper::get_instance();
		$result = array();
		foreach($helper->get_locations() as $loc)
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