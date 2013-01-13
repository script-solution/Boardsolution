<?php
/**
 * Contains the addfields-dao-class
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
 * The DAO-class for the add-fields-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_AddFields extends FWS_Singleton
{
	/**
	 * @return BS_DAO_AddFields the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns the field with given id
	 *
	 * @param int $id the id
	 * @return array|bool the row or false if not found
	 */
	public function get_by_id($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		$row = $db->get_row(
			'SELECT * FROM '.BS_TB_USER_FIELDS.' WHERE id = '.$id
		);
		if(!$row)
			return false;
		
		return $row;
	}
	
	/**
	 * Creates a new entry with the given fields
	 *
	 * @param array $fields all fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_USER_FIELDS,$fields);
	}
	
	/**
	 * Updates all given fields in the entry with given id
	 *
	 * @param int $id the id
	 * @param array $fields all fields to set
	 * @return int the number of affected rows
	 */
	public function update($id,$fields)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_USER_FIELDS,'WHERE id = '.$id,$fields);
	}
	
	/**
	 * Decrements the sort by 1 for all fields which sort is greater than <var>$lower</var>
	 *
	 * @param int $lower the lower value
	 * @return int the number of affected rows
	 */
	public function dec_sort($lower)
	{
		$db = FWS_Props::get()->db();

		return $db->update(BS_TB_USER_FIELDS,'WHERE field_sort > '.$lower,array(
			'field_sort' => array('field_sort - 1')
		));
	}
	
	/**
	 * Updates the sort of the entry with given id
	 *
	 * @param int $sort the new sort-value
	 * @param int $id the id
	 * @return int the number of affected rows
	 */
	public function update_sort_by_id($sort,$id)
	{
		$db = FWS_Props::get()->db();

		return $db->update(BS_TB_USER_FIELDS,'WHERE id = '.$id,array(
			'field_sort' => $sort
		));
	}
	
	/**
	 * Deletes the entry with given id
	 *
	 * @param int $id the id
	 * @return int the number of affected rows
	 */
	public function delete($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		$db->execute(
			'DELETE FROM '.BS_TB_USER_FIELDS.' WHERE id = '.$id
		);
		return $db->get_affected_rows();
	}
}
?>