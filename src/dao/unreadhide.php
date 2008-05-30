<?php
/**
 * Contains the unread-hide-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the unread-hide-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_UnreadHide extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_UnreadHide the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns all entries for the given user
	 *
	 * @param int $uid the user-id
	 * @return array the entries
	 */
	public function get_all_of_user($uid)
	{
		if(!PLIB_Helper::is_integer($uid) || $uid <= 0)
			PLIB_Helper::def_error('intgt0','uid',$uid);
		
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_UNREAD_HIDE.'
			 WHERE user_id = '.$uid
		);
	}
	
	/**
	 * Creates a new entry for the given user
	 *
	 * @param int $uid the user-id
	 * @param array $fids all forums to hide for unread
	 */
	public function create($uid,$fids)
	{
		if(count($fids) == 0)
			return;
		
		if(!PLIB_Helper::is_integer($uid) || $uid <= 0)
			PLIB_Helper::def_error('intgt0','uid',$uid);
		if(!PLIB_Array_Utils::is_integer($fids))
			PLIB_Helper::def_error('intarray','fids',$fids);
		
		$sql = 'INSERT INTO '.BS_TB_UNREAD_HIDE.' (forum_id,user_id) VALUES ';
		foreach($fids as $fid)
			$sql .= '('.$fid.','.$uid.'),';
		$sql = PLIB_String::substr($sql,0,-1);
		$this->db->sql_qry($sql);
	}
	
	/**
	 * Deletes all entries for the given users
	 *
	 * @param array $uids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($uids)
	{
		if(!PLIB_Array_Utils::is_integer($uids) || count($uids) == 0)
			PLIB_Helper::def_error('intarray>0','uids',$uids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_UNREAD_HIDE.' WHERE user_id IN ('.implode(',',$uids).')'
		);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries for the given forums
	 *
	 * @param array $fids the forum-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_forums($fids)
	{
		if(!PLIB_Array_Utils::is_integer($fids) || count($fids) == 0)
			PLIB_Helper::def_error('intarray>0','fids',$fids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_UNREAD_HIDE.' WHERE forum_id IN ('.implode(',',$fids).')'
		);
		return $this->db->get_affected_rows();
	}
}
?>