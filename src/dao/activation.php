<?php
/**
 * Contains the activation-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the activation-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Activation extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Activation the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Checks wether the given user-id and key exists
	 *
	 * @param int $id the user-id
	 * @param string $key the key
	 * @return boolean true if the entry exists
	 */
	public function exists($id,$key)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		return $db->sql_num(
			BS_TB_ACTIVATION,'user_id',' WHERE user_id = '.$id.' AND user_key = "'.$key.'"'
		) > 0;
	}
	
	/**
	 * Returns the entry of the given user
	 *
	 * @param int $id the user-id
	 * @return array the entry
	 */
	public function get_by_user($id)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$row = $db->sql_fetch(
			'SELECT * FROM '.BS_TB_ACTIVATION.' WHERE user_id = '.$id
		);
		if(!$row)
			return false;
		return $row;
	}
	
	/**
	 * Returns all "timed out" entries
	 *
	 * @param int $timeout the timeout in seconds
	 * @return array the timed out entries
	 */
	public function get_timedout_entries($timeout)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($timeout) || $timeout <= 0)
			PLIB_Helper::def_error('intgt0','timeout',$timeout);
		
		return $db->sql_rows(
			'SELECT * FROM '.BS_TB_ACTIVATION.' a
			 LEFT JOIN '.BS_TB_PROFILES.' p ON a.user_id = p.id
			 WHERE p.active = 0 AND p.registerdate < '.(time() - $timeout)
		);
	}
	
	/**
	 * Creates a new entry with the given user-id and key
	 *
	 * @param int $id the user-id
	 * @param string $key the key
	 * @return int the used id
	 */
	public function create($id,$key)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$db->sql_insert(BS_TB_ACTIVATION,array(
			'user_id' => $id,
			'user_key' => $key
		));
		return $db->get_last_insert_id();
	}
	
	/**
	 * Deletes the entry with given user-id and key
	 *
	 * @param int $id the user-id
	 * @param string $key the key
	 * @return int the number of affected rows
	 */
	public function delete($id,$key)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_ACTIVATION.' WHERE user_id = '.$id.' AND user_key = "'.$key.'"'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries of the given users
	 *
	 * @param array $ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($ids)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_ACTIVATION.' WHERE user_id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>