<?php
/**
 * Contains the user-class for the community-exchange
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Represents a user in Boardsolution. Note that all values in the objects of this class will
 * be NOT escaped. So you have to do that if you insert them into a database or something like that.
 *
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Community_User extends PLIB_Object
{
	/**
	 * Determines the user-status from the given groups-array
	 *
	 * @param array $groups a numeric array with the user-groups
	 * @return int the user-status
	 */
	public static function get_status_from_groups($groups)
	{
		if(in_array(BS_STATUS_ADMIN,$groups))
			return self::STATUS_ADMIN;
		else
			return self::STATUS_USER;
	}
	
	/**
	 * Creates an instance of this class with the given user-data
	 *
	 * @param array $data the user-data
	 * @return BS_Community_User the instance
	 */
	public static function get_instance_from_data($data)
	{
		$groups = PLIB_Array_Utils::advanced_explode(',',$data['user_group']);
		$status = self::get_status_from_groups($groups);
		return new BS_Community_User(
			$data['id'],$data['user_name'],$data['user_email'],$status,md5($data['user_pw'])
		);
	}
	
	/**
	 * Represents an administrator
	 */
	const STATUS_ADMIN		= 0;
	
	/**
	 * Represents an user
	 */
	const STATUS_USER			= 1;
	
	/**
	 * The id of the user
	 *
	 * @var int
	 */
	private $_id;
	
	/**
	 * The name of the user
	 *
	 * @var string
	 */
	private $_name;
	
	/**
	 * The (plain!) password of the user
	 *
	 * @var string
	 */
	private $_pw_plain;
	
	/**
	 * The md5-hash of the password
	 *
	 * @var string
	 */
	private $_pw_hash;
	
	/**
	 * The email of the user
	 *
	 * @var string
	 */
	private $_email;
	
	/**
	 * The user-status
	 *
	 * @var int
	 */
	private $_status;
	
	/**
	 * Constructor
	 *
	 * @param int $id the user-id
	 * @param string $name the user-name
	 * @param string $email the email-address
	 * @param int $status the user-status (self::STATUS_USER or self::STATUS_ADMIN)
	 * @param string $pw_hash the md5-hash of the password
	 * @param string $pw_plain the plain password (null = not available)
	 */
	public function __construct($id,$name,$email,$status,$pw_hash,$pw_plain = null)
	{
		parent::__construct();
		
		$this->_id = $id;
		$this->_name = $name;
		$this->_status = $status;
		$this->_pw_plain = $pw_plain;
		$this->_pw_hash = $pw_hash;
		$this->_email = $email;
	}

	/**
	 * @return int the user-id
	 */
	public function get_id()
	{
		return $this->_id;
	}

	/**
	 * @return string the user-name
	 */
	public function get_name()
	{
		return $this->_name;
	}

	/**
	 * @return string the email-address
	 */
	public function get_email()
	{
		return $this->_email;
	}

	/**
	 * @return string the md5-hash of the password
	 */
	public function get_pw_hash()
	{
		return $this->_pw_hash;
	}

	/**
	 * @return string the plain password (null if not available)
	 */
	public function get_pw_plain()
	{
		return $this->_pw_plain;
	}
	
	/**
	 * @return int the user-status (self::STATUS_USER or self::STATUS_ADMIN)
	 */
	public function get_status()
	{
		return $this->_status;
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>