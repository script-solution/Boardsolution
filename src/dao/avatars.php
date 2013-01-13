<?php
/**
 * Contains the avatars-dao-class
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
class BS_DAO_Avatars extends FWS_Singleton
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
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_AVATARS,'id','');
	}
	
	/**
	 * @param string $keyword the keyword
	 * @return int the number of avatars that match the given keyword
	 */
	public function get_count_for_keyword($keyword)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(
			BS_TB_AVATARS.' a','a.id','LEFT JOIN '.BS_TB_USER.' u ON a.user = u.`'.BS_EXPORT_USER_ID.'`
				WHERE u.`'.BS_EXPORT_USER_NAME.'` LIKE "%'.$keyword.'%" OR av_pfad LIKE "%'.$keyword.'%"'
		);
	}
	
	/**
	 * @param int $user_id the user-id
	 * @return int the number of avatars of the given user
	 */
	public function get_count_of_user($user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->get_row_count(BS_TB_AVATARS,'id',' WHERE user = '.$user_id);
	}
	
	/**
	 * @param int $user_id the user-id
	 * @return int the number of avatars that are usable by the given user (so the owner may
	 * 	be the administrator, too)
	 */
	public function get_count_for_user($user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->get_row_count(BS_TB_AVATARS,'id',' WHERE user = '.$user_id.' OR user = 0');
	}
	
	/**
	 * @return array all avatars
	 */
	public function get_all()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows('SELECT * FROM '.BS_TB_AVATARS);
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
		return $this->get_list_impl('',$start,$count);
	}
	
	/**
	 * Returns a list with all avatars that match the given keyword from <var>$start</var> to
	 * <var>$start + $count</var>.
	 *
	 * @param string $keyword the keyword
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of avatars (for the LIMIT-statement). 0 = unlimited
	 * @return array the avatar-list
	 */
	public function get_list_for_keyword($keyword,$start = 0,$count = 0)
	{
		return $this->get_list_impl(
			'WHERE user_name LIKE "%'.$keyword.'%" OR av_pfad LIKE "%'.$keyword.'%"',$start,$count
		);
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
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $this->get_list_impl('WHERE a.user = '.$user_id.' OR a.user = 0',$start,$count);
	}
	
	/**
	 * Returns the avatar with the given id
	 *
	 * @param int $id the avatar-id
	 * @return array|bool the avatar or false if not found
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(empty($path))
			FWS_Helper::def_error('notempty','path',$path);
		if(!FWS_Helper::is_integer($user_id) || $user_id < 0)
			FWS_Helper::def_error('intge0','user_id',$user_id);
		
		return $db->insert(BS_TB_AVATARS,array(
			'av_pfad' => $path,
			'user' => $user_id
		));
	}
	
	/**
	 * Deletes all avatars with the given ids
	 *
	 * @param array $ids the avatar-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_AVATARS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$db->execute(
			'DELETE FROM '.BS_TB_AVATARS.' WHERE id IN ('.implode(',',$ids).') AND user = '.$user_id
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all avatars of the given users
	 *
	 * @param array $user_ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($user_ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($user_ids) || count($user_ids) == 0)
			FWS_Helper::def_error('intarray>0','user_ids',$user_ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_AVATARS.' WHERE user IN ('.implode(',',$user_ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Builds the avatar-list with the given WHERE-clause
	 *
	 * @param string $where the WHERE-clause
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of avatars (for the LIMIT-statement). 0 = unlimited
	 * @return array the avatar-list
	 */
	protected function get_list_impl($where,$start,$count)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
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