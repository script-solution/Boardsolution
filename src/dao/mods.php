<?php
/**
 * Contains the mods-dao-class
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
 * The DAO-class for the mods-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Mods extends FWS_Singleton
{
	/**
	 * @return BS_DAO_Mods the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Checks wether the given user is moderator in the given forum
	 *
	 * @param int $user_id the user-id
	 * @param int $fid the forum-id
	 * @return boolean true if so
	 */
	public function is_user_mod_in_forum($user_id,$fid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		
		return $db->get_row_count(BS_TB_MODS,'id',' WHERE rid = '.$fid.' AND user_id = '.$user_id) > 0;
	}
	
	/**
	 * Returns all entries for the given user-ids
	 *
	 * @param array $user_ids the user-ids
	 * @return array all found rows
	 */
	public function get_by_user_ids($user_ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($user_ids) || count($user_ids) == 0)
			FWS_Helper::def_error('intarray>0','user_ids',$user_ids);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_MODS.'
			 WHERE user_id IN ('.implode(',',$user_ids).')'
		);
	}
	
	/**
	 * Returns all entries grouped by the user-id. You'll get all from the mods-table, the user-group
	 * and the user-name.
	 *
	 * @return array the found rows
	 */
	public function get_all_grouped_by_user()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT m.*,p.user_group,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_MODS.' m
			 LEFT JOIN '.BS_TB_USER.' u ON m.user_id = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON m.user_id = p.id
			 GROUP BY m.user_id'
		);
	}
	
	/**
	 * Creates a new entry for the given forum and user
	 *
	 * @param int $fid the forum-id
	 * @param int $user_id the user-id
	 * @return int the used id
	 */
	public function create($fid,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->insert(BS_TB_MODS,array(
			'rid' => $fid,
			'user_id' => $user_id
		));
	}
	
	/**
	 * Creates entries for all given forum-id with the given user
	 *
	 * @param array $fids the forum-ids
	 * @param int $user_id the user-id
	 */
	public function create_multiple($fids,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($fids) || count($fids) == 0)
			FWS_Helper::def_error('intarray>0','fids',$fids);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$sql = 'INSERT INTO '.BS_TB_MODS.' (user_id,rid) VALUES ';
		foreach($fids as $fid)
			$sql .= '('.$user_id.','.$fid.'),';
		$sql = FWS_String::substr($sql,0,-1);
		$db->execute($sql);
	}
	
	/**
	 * Deletes the entry for the given user and forum
	 *
	 * @param int $user_id the user-id
	 * @param int $fid the forum-id
	 * @return int the number of affected rows
	 */
	public function delete_user_from_forum($user_id,$fid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		
		$db->execute(
			'DELETE FROM '.BS_TB_MODS.' WHERE rid = '.$fid.' AND user_id = '.$user_id
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries of the given users
	 *
	 * @param array $user_ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($user_ids)
	{
		return $this->delete_by('user_id',$user_ids);
	}
	
	/**
	 * Deletes all entries of the given forums
	 *
	 * @param array $fids the forum-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_forums($fids)
	{
		return $this->delete_by('rid',$fids);
	}
	
	/**
	 * Deletes entries by the given field
	 *
	 * @param string $field the field-name
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	protected function delete_by($field,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_MODS.' WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>