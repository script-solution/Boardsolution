<?php
/**
 * Contains the user-bans-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the user-bans-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_UserBans extends FWS_Singleton
{
	/**
	 * @return BS_DAO_UserBans the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Checks wether <var>$uid1</var> has baned <var>$uid2</var>
	 *
	 * @param int $uid1 the first user
	 * @param int $uid2 the second user
	 * @return boolean true if so
	 */
	public function has_baned($uid1,$uid2)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($uid1) || $uid1 <= 0)
			FWS_Helper::def_error('intgt0','uid1',$uid1);
		if(!FWS_Helper::is_integer($uid2) || $uid2 <= 0)
			FWS_Helper::def_error('intgt0','uid2',$uid2);
		
		return $db->get_row_count(
			BS_TB_USER_BANS,'id',' WHERE user_id = '.$uid1.' AND baned_user = '.$uid2
		) > 0;
	}
	
	/**
	 * Returns the given baned users of the given user
	 *
	 * @param int $id the user-id
	 * @param array $ids the baned users
	 * @return array the found entries
	 */
	public function get_by_user($id,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT b.*,u.`'.BS_EXPORT_USER_NAME.'` user_name,p.user_group
			 FROM '.BS_TB_USER_BANS.' b
			 LEFT JOIN '.BS_TB_USER.' u ON b.baned_user = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON b.baned_user = p.id
			 WHERE b.id IN ('.implode(',',$ids).') AND b.user_id = '.$id
		);
	}
	
	/**
	 * Returns all rows of the given user
	 *
	 * @param int $id the user-id
	 * @return array the rows
	 */
	public function get_all_of_user($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->get_rows(
			'SELECT b.*,u.`'.BS_EXPORT_USER_NAME.'` user_name,p.user_group
			 FROM '.BS_TB_USER_BANS.' b
			 LEFT JOIN '.BS_TB_USER.' u ON b.baned_user = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON b.baned_user = p.id
			 WHERE b.user_id = '.$id
		);
	}
	
	/**
	 * Creates a new entry so that <var>$uid1</var> has baned <var>$uid2</var>.
	 *
	 * @param int $uid1 the first user
	 * @param int $uid2 the second user
	 * @return int the used id
	 */
	public function create($uid1,$uid2)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($uid1) || $uid1 <= 0)
			FWS_Helper::def_error('intgt0','uid1',$uid1);
		if(!FWS_Helper::is_integer($uid2) || $uid2 <= 0)
			FWS_Helper::def_error('intgt0','uid2',$uid2);
		
		return $db->insert(BS_TB_USER_BANS,array(
			'user_id' => $uid1,
			'baned_user' => $uid2
		));
	}
	
	/**
	 * Deletes the bans with given ids of the given user
	 *
	 * @param int $id the user-id
	 * @param array $ids the ban-ids
	 * @return int the number of affected rows
	 */
	public function delete_bans_of_user($id,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_USER_BANS.'
			 WHERE user_id = '.$id.' AND id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all bans which contain the given user-ids
	 *
	 * @param array $ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_USER_BANS.'
			 WHERE user_id IN ('.implode(',',$ids).') OR baned_user IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>