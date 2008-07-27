<?php
/**
 * Contains the change-email-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
class BS_DAO_ChangeEmail extends PLIB_Singleton
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
	 * @return array the entry or false if not found
	 */
	public function get_by_user($id,$key = '')
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$row = $db->sql_fetch(
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
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$db->sql_insert(BS_TB_CHANGE_EMAIL,array(
			'user_id' => $id,
			'user_key' => $key,
			'email_address' => $email,
			'email_date' => time()
		));
		return $db->get_last_insert_id();
	}
	
	/**
	 * Deletes all entries of the given user-id
	 *
	 * @param int $id the used-id
	 * @return int the number of affected rows
	 */
	public function delete_by_user($id)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$db->sql_qry(
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
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($timeout) || $timeout <= 0)
			PLIB_Helper::def_error('intgt0','timeout',$timeout);
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_CHANGE_EMAIL.'
			 WHERE email_date < '.(time() - $timeout)
		);
		return $db->get_affected_rows();
	}
}
?>