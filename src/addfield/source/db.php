<?php
/**
 * Contains the db-source-class for the additional-fields
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The db-based implementation of the additional-fields-source
 * 
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_AddField_Source_DB extends PLIB_Object implements PLIB_AddField_Source
{
	public function get_fields()
	{
		$cache = PLIB_Props::get()->cache();

		$fields = array();
		foreach($cache->get_cache('user_fields') as $data)
		{
			$fields[] = new BS_AddField_Data(
				$data['id'],$data['field_type'],$data['field_show_type'],$data['field_name'],
				$data['display_name'],$data['field_sort'],$data['field_is_required'],
				$data['field_edit_notice'],$data['field_suffix'],$data['display_always'],
				$data['field_length'],PLIB_Array_Utils::advanced_explode("\n",$data['allowed_values']),
				$data['field_validation'],$data['field_custom_display']
			);
		}
		return $fields;
	}
	
	public function get_field($data)
	{
		switch($data->get_type())
		{
			case 'int':
				return new PLIB_AddField_Type_Int($data);
			case 'date':
				return new PLIB_AddField_Type_Date($data);
			case 'line':
				return new PLIB_AddField_Type_Line($data);
			case 'text':
				return new PLIB_AddField_Type_Text($data);
			case 'enum':
				return new PLIB_AddField_Type_Enum($data);
			default:
				PLIB_Helper::error('Unknown field-type "'.$data->get_type().'"!');
				return null;
		}
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>