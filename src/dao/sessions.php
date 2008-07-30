<?php
/**
 * Contains the sessions-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the sessions-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Sessions extends FWS_Singleton
{
	/**
	 * @return BS_DAO_Sessions the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Checks wether the given key which is the md5-hash of session-id and user-ip is equal
	 * to the one in the session-table of the given user
	 *
	 * @param int $user_id the user-id
	 * @param string $key the key
	 * @return boolean true if so
	 */
	public function check_sessionip_key($user_id,$key)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->sql_num(
			BS_TB_SESSIONS,
			'user_id',
			' WHERE user_id = '.$user_id." AND MD5(CONCAT(session_id,user_ip)) = '".$key."'"
		) > 0;
	}
	
	/**
	 * Returns all entries sorted by date descending
	 *
	 * @return array the entries
	 */
	public function get_all()
	{
		$db = FWS_Props::get()->db();

		return $db->sql_rows(
			'SELECT s.*,u.`'.BS_EXPORT_USER_NAME.'` user_name,p.ghost_mode,p.user_group
			 FROM '.BS_TB_SESSIONS.' s
			 LEFT JOIN '.BS_TB_USER.' u ON s.user_id = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON s.user_id = p.id
			 ORDER BY s.date DESC'
		);
	}
	
	/**
	 * Creates a new entry with the given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		$db->sql_insert(BS_TB_SESSIONS,$fields);
		return $db->get_last_insert_id();
	}
	
	/**
	 * Updates the entry with given session-id
	 *
	 * @param string $sid the session-id
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_sid($sid,$fields)
	{
		$db = FWS_Props::get()->db();

		$db->sql_update(BS_TB_SESSIONS,'WHERE session_id = "'.$sid.'"',$fields);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all rows with given session-ids
	 *
	 * @param array $sids the session-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_sids($sids)
	{
		$db = FWS_Props::get()->db();

		if(!is_array($sids) || count($sids) == 0)
			FWS_Helper::def_error('array>0','sids',$sids);
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_SESSIONS.' WHERE session_id IN ("'.implode('","',$sids).'")'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all rows with given user-ids
	 *
	 * @param array $ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_SESSIONS.' WHERE user_id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>