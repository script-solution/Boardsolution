<?php
/**
 * Contains the change-pw-dao-class
 *
 * @version			$Id: changepw.php 796 2008-05-29 18:23:27Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the change-pw-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_ChangePW extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_ChangePW the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Checks wether an entry exists for the give user-id and optional the given key.
	 *
	 * @param int $id the used-id
	 * @param string $key the key (empty = ignore)
	 * @return boolean true if it exists
	 */
	public function exists($id,$key = '')
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		return $this->db->sql_num(
			BS_TB_CHANGE_PW,'user_id',' WHERE user_id = '.$id.($key ? ' AND user_key = "'.$key.'"' : '')
		) > 0;
	}
	
	/**
	 * Creates a new entry for the given user and key
	 *
	 * @param int $id the used-id
	 * @param string $key the key
	 * @return int the used id
	 */
	public function create($user_id,$key)
	{
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		$this->db->sql_insert(BS_TB_CHANGE_PW,array(
			'user_id' => $user_id,
			'user_key' => $key,
			'email_date' => time()
		));
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Updates the entry of the given user and sets the key to the given one and the date to now.
	 *
	 * @param int $id the used-id
	 * @param string $key the key
	 * @return int the number of affected rows
	 */
	public function update_by_user($user_id,$key)
	{
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		$this->db->sql_update(BS_TB_CHANGE_PW,'WHERE user_id = '.$user_id,array(
			'user_key' => $key,
			'email_date' => time()
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries of the given user-id
	 *
	 * @param int $id the used-id
	 * @return int the number of affected rows
	 */
	public function delete_by_user($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_qry('DELETE FROM '.BS_TB_CHANGE_PW.' WHERE user_id = '.$id);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all "timed out" entries
	 *
	 * @param int $timeout the timeout in seconds
	 * @return int the number of affected rows
	 */
	public function delete_timedout($timeout)
	{
		if(!PLIB_Helper::is_integer($timeout) || $timeout <= 0)
			PLIB_Helper::def_error('intgt0','timeout',$timeout);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_CHANGE_PW.'
			 WHERE email_date < '.(time() - $timeout)
		);
		return $this->db->get_affected_rows();
	}
}
?>