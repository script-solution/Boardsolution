<?php
/**
 * Contains the db session-storage-class
 *
 * @version			$Id: db.php 755 2008-05-24 18:07:53Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.session
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The db-based implementation for the session-storage
 * 
 * @package			Boardsolution
 * @subpackage	src.session
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Session_Storage_DB extends PLIB_FullObject implements PLIB_Session_Storage
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
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>