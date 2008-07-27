<?php
/**
 * Contains the additional-fields manager for boardsolution
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The additional fields manager for Boardsolution
 *
 * @package			Boardsolution
 * @subpackage	src.addfield
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_AddField_Manager extends PLIB_AddField_Manager
{
	/**
	 * @return BS_AddField_Manager the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(new BS_AddField_Source_DB());
	}
	
	/**
	 * Checks wether any required field is empty, that can be edited in the profile
	 *
	 * @return boolean true if so
	 */
	public function is_any_required_field_empty()
	{
		$user = PLIB_Props::get()->user();

		foreach($this->get_fields_at(BS_UF_LOC_USER_PROFILE) as $field)
		{
			/* @var $field PLIB_AddField_Field */
			$data = $field->get_data();
			$stored_val = $user->get_profile_val('add_'.$data->get_name());
			if($data->is_required() && $field->is_empty($stored_val))
				return true;
		}
		
		return false;
	}
}
?>