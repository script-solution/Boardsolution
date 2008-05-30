<?php
/**
 * Contains the avatars-dao-class
 *
 * @version			$Id: avatars.php 737 2008-05-23 18:26:46Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the avatars-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Avatars extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Avatars the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the number of avatars
	 */
	public function get_count()
	{
		return $this->db->sql_num(BS_TB_AVATARS,'id','');
	}
	
	/**
	 * @param int $user_id the user-id
	 * @return int the number of avatars of the given user
	 */
	public function get_count_of_user($user_id)
	{
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		return $this->db->sql_num(BS_TB_AVATARS,'id',' WHERE user = '.$user_id);
	}
	
	/**
	 * @param int $user_id the user-id
	 * @return int the number of avatars that are usable by the given user (so the owner may
	 * 	be the administrator, too)
	 */
	public function get_count_for_user($user_id)
	{
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		return $this->db->sql_num(BS_TB_AVATARS,'id',' WHERE user = '.$user_id.' OR user = 0');
	}
	
	/**
	 * @return array all avatars
	 */
	public function get_all()
	{
		return $this->db->sql_rows('SELECT * FROM '.BS_TB_AVATARS);
	}
	
	/**
	 * Returns a list with all avatars from <var>$start</var> to <var>$start + $count</var>.
	 *
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of avatars (for the LIMIT-statement). 0 = unlimited
	 * @return array the avatar-list
	 */
	public function get_list($start = 0,$count = 0)
	{
		return $this->_get_list('',$start,$count);
	}
	
	/**
	 * Returns a list with all avatars which are usable by the given user
	 * from <var>$start</var> to <var>$start + $count</var>.
	 *
	 * @param int $user_id the user-id
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of avatars (for the LIMIT-statement). 0 = unlimited
	 * @return array the avatar-list
	 */
	public function get_list_for_user($user_id,$start = 0,$count = 0)
	{
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		return $this->_get_list('WHERE a.user = '.$user_id.' OR a.user = 0',$start,$count);
	}
	
	/**
	 * Returns the avatar with the given id
	 *
	 * @param int $id the avatar-id
	 * @return array the avatar or false if not found
	 */
	public function get_by_id($id)
	{
		$rows = $this->get_by_ids(array($id));
		if(count($rows) == 0)
			return false;
		
		return $rows[0];
	}
	
	/**
	 * Returns all avatars with the given ids
	 *
	 * @param array $ids the avatar-ids
	 * @return array the avatars
	 */
	public function get_by_ids($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_AVATARS.' WHERE id IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns all avatars of the given user and the given ids
	 *
	 * @param array $ids the avatar-ids
	 * @param int $user_id the user-id
	 * @return array the avatars
	 */
	public function get_by_ids_from_user($ids,$user_id)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_AVATARS.'
			 WHERE id IN ('.implode(',',$ids).') AND user = '.$user_id
		);
	}
	
	/**
	 * Creates a new avatar with the given values
	 *
	 * @param string $path the avatar-path
	 * @param int $user_id the user-id (0 = admin)
	 * @return int the used id
	 */
	public function create($path,$user_id = 0)
	{
		if(empty($path))
			PLIB_Helper::def_error('notempty','path',$path);
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		$this->db->sql_insert(BS_TB_AVATARS,array(
			'av_pfad' => $path,
			'user' => $user_id
		));
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Deletes all avatars with the given ids
	 *
	 * @param array $ids the avatar-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_AVATARS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all avatars with the given ids of the given user
	 *
	 * @param array $ids the avatar-ids
	 * @param int $user_id the user-id
	 * @return int the number of affected rows
	 */
	public function delete_by_ids_from_user($ids,$user_id)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_AVATARS.' WHERE id IN ('.implode(',',$ids).') AND user = '.$user_id
		);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all avatars of the given users
	 *
	 * @param array $user_ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($user_ids)
	{
		if(!PLIB_Array_Utils::is_integer($user_ids) || count($user_ids) == 0)
			PLIB_Helper::def_error('intarray>0','user_ids',$user_ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_AVATARS.' WHERE user IN ('.implode(',',$user_ids).')'
		);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Builds the avatar-list with the given WHERE-clause
	 *
	 * @param string $where the WHERE-clause
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of avatars (for the LIMIT-statement). 0 = unlimited
	 * @return array the avatar-list
	 */
	protected function _get_list($where,$start,$count)
	{
		if(!PLIB_Helper::is_integer($start) || $start < 0)
			PLIB_Helper::def_error('intge0','start',$start);
		if(!PLIB_Helper::is_integer($count) || $count < 0)
			PLIB_Helper::def_error('intge0','count',$count);
		
		return $this->db->sql_rows(
			'SELECT a.*,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_AVATARS.' a
			 LEFT JOIN '.BS_TB_USER.' u ON a.user = u.`'.BS_EXPORT_USER_ID.'`
			 '.$where.'
			 ORDER BY a.user DESC
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
}
?>