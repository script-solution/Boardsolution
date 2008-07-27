<?php
/**
 * Contains the helper-class for the additional-fields
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An helper-class for the additional-fields-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_AdditionalFields_Helper extends PLIB_Singleton
{
	/**
	 * @return BS_ACP_Module_AdditionalFields_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return array an numeric array with all possible locations
	 */
	public function get_locations()
	{
		return array(
			BS_UF_LOC_POSTS,BS_UF_LOC_REGISTRATION,BS_UF_LOC_USER_DETAILS,BS_UF_LOC_USER_PROFILE
		);
	}
	
	/**
	 * Collects the field-attributes from the POST-array and stores them in the given array
	 *
	 * @param int $id the field-id
	 * @param string $type 'edit' or 'add'
	 * @param array $values the result-array
	 * @return string the error-message if an error has occurred or an empty string
	 */
	public function retrieve_valid_field_attributes($id,$type,&$values)
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$cache = PLIB_Props::get()->cache();

		$manager  = BS_AddField_Manager::get_instance();
		$field = $id == 0 ? null : $manager->get_field($id);
		$locked = $id == 0 ? false : $field->get_data()->get_name() == 'birthday';
		
		$values['field_name'] = $input->get_var('field_name','post',PLIB_Input::STRING);
		$values['display_name'] = $input->get_var('display_name','post',PLIB_Input::STRING);
		$values['field_length'] = $input->get_var('field_length','post',PLIB_Input::INTEGER);
		$values['field_type'] = $input->correct_var(
			'field_type','post',PLIB_Input::STRING,array('int','line','text','date','enum'),'line'
		);
		$values['allowed_values'] = $input->get_var('field_values','post',PLIB_Input::STRING);
		$values['field_suffix'] = $input->get_var('field_suffix','post',PLIB_Input::STRING);
		$values['field_custom_display'] = PLIB_StringHelper::htmlspecialchars_back(
			$input->get_var('field_custom_display','post',PLIB_Input::STRING)
		);
		$values['field_validation'] = $input->get_var(
			'field_validation','post',PLIB_Input::STRING
		);
		$values['field_is_required'] = $input->get_var(
			'field_is_required','post',PLIB_Input::INT_BOOL
		);
		$values['field_edit_notice'] = $input->get_var(
			'field_edit_notice','post',PLIB_Input::STRING
		);
		$values['display_always'] = $input->get_var(
			'display_always','post',PLIB_Input::INT_BOOL
		);

		if(!$locked && !preg_match('/^[a-z0-9_]+$/i',$values['field_name']))
			return $locale->lang('field_name_invalid');

		if($type == 'add')
		{
			if($cache->get_cache('user_fields')->element_exists_with(
					array('field_name' => $values['field_name'])))
				return $locale->lang('field_name_exists');
		}

		if(trim($values['display_name']) == '')
			return $locale->lang('display_name_empty');

		if(!$locked && ($values['field_type'] == 'int' ||
			$values['field_type'] == 'line') && ($values['field_length'] == null ||
				$values['field_length'] <= 0 || $values['field_length'] > 255))
			return $locale->lang('field_length_invalid');

		if($values['field_type'] == 'enum')
		{
			$lines = array();
			$values['field_length'] = 0;
			$input_lines = explode("\n",$values['allowed_values']);
			for($i = 0;$i < count($input_lines);$i++)
			{
				if(trim($input_lines[$i]) != '')
					$lines[] = trim($input_lines[$i]);
			}

			if(count($lines) < 2)
				return $locale->lang('field_values_invalid');
			
			$values['allowed_values'] = implode("\n",$lines);
		}
		else if($values['field_type'] == 'text')
			$values['field_length'] = 0;

		$values['field_show_type'] = 0;
		foreach($this->get_locations() as $loc)
		{
			if($input->get_var('loc_'.$loc,'post',PLIB_Input::INT_BOOL) == 1)
				$values['field_show_type'] |= $loc;
		}
		
		// ensure that this fields will not be updated
		if($locked)
		{
			unset($values['field_name']);
			unset($values['field_type']);
			unset($values['field_length']);
			unset($values['allowed_values']);
		}

		return '';
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>