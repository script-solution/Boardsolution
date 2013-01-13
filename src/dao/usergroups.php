<?php
/**
 * Contains the user-groups-dao-class
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
 * The DAO-class for the user-groups-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_UserGroups extends FWS_Singleton
{
	/**
	 * @return BS_DAO_UserGroups the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns the data of the group with given id
	 *
	 * @param int $id the group-id
	 * @return array|bool the data of the group or false if not found
	 */
	public function get_by_id($id)
	{
		$rows = $this->get_by_ids(array($id));
		if(count($rows) == 0)
			return false;
		return $rows[0];
	}
	
	/**
	 * Returns all rows with given ids
	 *
	 * @param array $ids the group-ids
	 * @return array the groups
	 */
	public function get_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_USER_GROUPS.' WHERE id IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Creates a new group with given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_USER_GROUPS,$fields);
	}
	
	/**
	 * Updates the given fields of the group with given id
	 *
	 * @param int $id the group-id
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_id($id,$fields)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_USER_GROUPS,'WHERE id = '.$id,$fields);
	}
	
	/**
	 * Deletes the groups with given ids
	 *
	 * @param array $ids the group-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_USER_GROUPS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>