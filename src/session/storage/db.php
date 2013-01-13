<?php
/**
 * Contains the db session-storage-class
 * 
 * @package			Boardsolution
 * @subpackage	src.session
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
 * The db-based implementation for the session-storage
 * 
 * @package			Boardsolution
 * @subpackage	src.session
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Session_Storage_DB extends FWS_Object implements FWS_Session_Storage
{
	public function load_list()
	{
		$online = array();
		foreach(BS_DAO::get_sessions()->get_all() as $data)
			$online[] = new BS_Session_Data($data);
		return $online;
	}
	
	public function get_new_user()
	{
		return new BS_Session_Data(array(
			'session_id' => '',
			'user_id' => 0,
			'user_ip' => '',
			'user_name' => '',
			'date' => time(),
			'user_agent' => '',
			'session_data' => serialize(array()),
			'location' => '',
			'ghost_mode' => 0,
			'user_group' => ''
		));
	}
	
	public function add_user($user)
	{
		BS_DAO::get_sessions()->create($this->_get_fields($user));
	}
	
	public function update_user($user)
	{
		BS_DAO::get_sessions()->update_by_sid($user->get_session_id(),$this->_get_fields($user));
	}
	
	public function remove_user($ids)
	{
		BS_DAO::get_sessions()->delete_by_sids($ids);
	}
	
	/**
	 * Builds the fields to store
	 * 
	 * @param BS_Session_Data $user
	 * @return array the fields to store
	 */
	private function _get_fields($user)
	{
		return array(
			'session_id' => $user->get_session_id(),
			'user_id' => $user->get_user_id(),
			'user_ip' => $user->get_user_ip(),
			'user_agent' => $user->get_user_agent(),
			'date' => $user->get_date(),
			'location' => addslashes($user->get_location()),
			'session_data' => addslashes($user->get_session_data())
		);
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>