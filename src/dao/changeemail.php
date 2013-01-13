<?php
/**
 * Contains the change-email-dao-class
 * 
 * @package			Boardsolution
 * @subpackage	src.dao
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
 * The DAO-class for the change-email-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_ChangeEmail extends FWS_Singleton
{
	/**
	 * @return BS_DAO_ChangeEmail the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns the entry for the given user. Optional you can specify the key
	 *
	 * @param int $id the user-id
	 * @param string $key the key (empty = ignore)
	 * @return array|bool the entry or false if not found
	 */
	public function get_by_user($id,$key = '')
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		$row = $db->get_row(
			'SELECT * FROM '.BS_TB_CHANGE_EMAIL.'
			 WHERE user_id = '.$id.($key ? ' AND user_key = "'.$key.'"' : '')
		);
		if(!$row)
			return false;
		return $row;
	}
	
	/**
	 * Creates a new entry for the given user, key and email
	 *
	 * @param int $id the used-id
	 * @param string $key the key
	 * @param string $email the email-address
	 * @return int the used id
	 */
	public function create($id,$key,$email)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->insert(BS_TB_CHANGE_EMAIL,array(
			'user_id' => $id,
			'user_key' => $key,
			'email_address' => $email,
			'email_date' => time()
		));
	}
	
	/**
	 * Deletes all entries of the given user-id
	 *
	 * @param int $id the used-id
	 * @return int the number of affected rows
	 */
	public function delete_by_user($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		$db->execute(
			'DELETE FROM '.BS_TB_CHANGE_EMAIL.'
			 WHERE user_id = '.$id
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all "timed out" entries
	 *
	 * @param int $timeout the timeout in seconds
	 * @return int the number of affected rows
	 */
	public function delete_timedout($timeout)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($timeout) || $timeout <= 0)
			FWS_Helper::def_error('intgt0','timeout',$timeout);
		
		$db->execute(
			'DELETE FROM '.BS_TB_CHANGE_EMAIL.'
			 WHERE email_date < '.(time() - $timeout)
		);
		return $db->get_affected_rows();
	}
}
?>