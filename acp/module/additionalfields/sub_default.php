<?php
/**
 * Contains the default-submodule for additionalfields
 * 
 * @version			$Id: sub_default.php 765 2008-05-24 21:14:51Z nasmussen $
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
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_ADDFIELDS => 'delete',
			BS_ACP_ACTION_SWITCH_ADDFIELDS => 'switch'
		);
	}
	
	public function run()
	{
		if(($delete = $this->input->get_var('delete','post')) != null)
		{
			$rows = $this->cache->get_cache('user_fields')->get_field_vals_of_keys($delete,'display_name');
			$namelist = PLIB_StringHelper::get_enum($rows,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_ADDFIELDS.'&amp;ids='.implode(',',$delete)
				),
				$this->url->get_acpmod_url()
			);
		}
		
		$fields = $this->cache->get_cache('user_fields');
		
		$sort_options = array();
		$num = $fields->get_element_count();
		for($i = 1;$i <= $num;$i++)
			$sort_options[$i] = $i;
		
		$this->tpl->add_array('sort_options',$sort_options);
		$this->_request_formular();
		
		$i = 0;
		$tplfields = array();
		$elements = array_values($fields->get_elements());
		foreach($elements as $data)
		{
			$field_type = $this->_get_field_type_name($data['field_type']);
			if($data['field_type'] != 'enum' && $data['field_type'] != 'text' && $data['field_type'] != 'date')
				$field_type .= ' ( '.$data['field_length'].' )';
			$display = $this->_get_display_locations_images($data['field_show_type']);
			
			if($i > 0)
			{
				$up_ids = $elements[$i - 1]['id'].','.$data['id'];
				$switch_up_url = $this->url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_SWITCH_ADDFIELDS.'&amp;ids='.$up_ids
				);
			}
			else
				$switch_up_url = '';

			if($i < count($elements) - 1)
			{
				$down_ids = $elements[$i + 1]['id'].','.$data['id'];
				$switch_down_url = $this->url->get_acpmod_url(
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
		
		$this->tpl->add_array('fields',$tplfields);
	}

	/**
	 * determines the name of the field-type
	 *
	 * @param string $type the field-type
	 * @return string the name of the given type
	 */
	private function _get_field_type_name($type)
	{
		switch($type)
		{
			case 'date':
				return $this->locale->lang('field_type_date');
			
			case 'int':
				return $this->locale->lang('field_type_int');

			case 'line':
				return $this->locale->lang('field_type_line');

			case 'text':
				return $this->locale->lang('field_type_text');

			default:
				return $this->locale->lang('field_type_enum');
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
				$result[] = '<img src="'.PLIB_Path::inner().'acp/images/ok.gif" alt="ok" />';
			else
				$result[] = '<img src="'.PLIB_Path::inner().'acp/images/failed.gif" alt="failed" />';
		}

		return $result;
	}
	
	public function get_location()
	{
		return array();
	}
}
?>