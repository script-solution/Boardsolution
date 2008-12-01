<?php
/**
 * Contains the user-data-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.user
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Adds the profile-data to the user-data-class
 * 
 * @package			Boardsolution
 * @subpackage	src.user
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_User_Data extends FWS_User_Data
{
	/**
	 * Additional user-data
	 *
	 * @var array
	 */
	private $_data = array();
	
	/**
	 * Constructor
	 * 
	 * @param array $data the userdata
	 */
	public function __construct($data)
	{
		$cfg = FWS_Props::get()->cfg();

		parent::__construct($data['id'],$data['user_name'],$data['user_pw']);
		
		$this->_data = $data;
		if($cfg['enable_pms'] == 1)
			$this->_data['unread_pms'] = BS_DAO::get_pms()->get_unread_pms_count($this->_data['id']);
		else
			$this->_data['unread_pms'] = 0;

		// ensure that we have no null-values in the array
		// TODO keep that or should #get_profile_val() return null / '' if a value is not set?
		foreach($this->_data as $name => $val)
		{
			if($val === null)
				$this->_data[$name] = '';
		}
	}
	
	/**
	 * @return array an associative array with all fields
	 */
	public function get_all_fields()
	{
		return $this->_data;
	}
	
	/**
	 * Returns the value of the profile-table-field with given name
	 *
	 * @param string $name the field-name
	 * @return string the value
	 */
	public function get_profile_val($name)
	{
		if(!isset($this->_data[$name]))
			FWS_Helper::error('The profile-field "'.$name.'" does not exist!');
		
		return $this->_data[$name];
	}
	
	/**
	 * Sets the value of the profile-table-field with given name to given value
	 *
	 * @param string $name the field-name
	 * @param string $value the new value
	 */
	public function set_profile_val($name,$value)
	{
		if(!isset($this->_data[$name]))
			FWS_Helper::error('The profile-field "'.$name.'" does not exist!');
		if($value !== null && !is_scalar($value))
			FWS_Helper::def_error('scalar','value',$value);
		
		$this->_data[$name] = $value;
	}
	
	protected function get_dump_vars()
	{
		return array_merge(parent::get_dump_vars(),get_object_vars($this));
	}
}
?>