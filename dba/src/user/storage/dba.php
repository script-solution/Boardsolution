<?php
/**
 * Contains the dba-user-storage-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The user-storage-implementation for the db-admin-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_User_Storage_DBA extends FWS_Object implements FWS_User_Storage
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		include_once(FWS_Path::server_app().'dba/access.php');
	}
	
	/**
	 * @see FWS_User_Storage::check_user()
	 *
	 * @param unknown_type $userdata
	 * @return int
	 */
	public function check_user($userdata)
	{
		// nothing to check
		return FWS_User_Current::LOGIN_ERROR_NO_ERROR;
	}

	/**
	 * @see FWS_User_Storage::get_hash_of_pw()
	 *
	 * @param string $pw
	 * @param FWS_User_Data $data
	 * @return string
	 */
	public function get_hash_of_pw($pw,$data)
	{
		// we don't store the hash of the pw
		return $pw;
	}

	/**
	 * @see FWS_User_Storage::get_userdata_by_id()
	 *
	 * @param int $id
	 * @return FWS_User_Data
	 */
	public function get_userdata_by_id($id)
	{
		// if the user is loggedin we want to get it's user-data by id.
		// therefore we always return the data
		return new FWS_User_Data(
			1, // always use 1 as id
			BS_DBA_USERNAME,
			BS_DBA_PASSWORD
		);
	}

	/**
	 * @see FWS_User_Storage::get_userdata_by_name()
	 *
	 * @param string $name
	 * @return FWS_User_Data
	 */
	public function get_userdata_by_name($name)
	{
		if($name == BS_DBA_USERNAME)
		{
			return new FWS_User_Data(
				1, // always use 1 as id
				BS_DBA_USERNAME,
				BS_DBA_PASSWORD
			);
		}
		return null;
	}

	/**
	 * @see FWS_User_Storage::login()
	 *
	 * @param int $id
	 */
	public function login($id)
	{
		// nothing to do
	}

	/**
	 * @see FWS_User_Storage::logout()
	 *
	 * @param int $id
	 */
	public function logout($id)
	{
		// nothing to do
	}

	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>