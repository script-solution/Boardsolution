<?php
/**
 * Contains the dba-user-storage-class
 * 
 * @package			Boardsolution
 * @subpackage	dba.src
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
	 * @see FWS_User_Storage::verify_password()
	 *
	 * @param string &$pw
	 * @param FWS_User_Data $data
	 * @return int
	 */
	public function check_password(&$pw,$data)
	{
		if($data !== null)
		{
			// we don't store the hash of the pw
			$hash = $data->get_user_pw();
			if(strcmp($pw,$hash) === 0)
			{
				$pw = $hash;
				return FWS_User_Current::LOGIN_ERROR_NO_ERROR;
			}
		}
		return FWS_User_Current::LOGIN_ERROR_PW_INCORRECT;
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

	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>