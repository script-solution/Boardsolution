<?php
/**
 * Contains the smileys-dao-class
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
 * The DAO-class for the smileys-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Smileys extends FWS_Singleton
{
	/**
	 * @return BS_DAO_Smileys the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the total number of smileys
	 */
	public function get_count()
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_SMILEYS,'*','');
	}
	
	/**
	 * Checks wether a smiley with given path exists
	 *
	 * @param string $path the path
	 * @return boolean true if the path exists
	 */
	public function path_exists($path)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_SMILEYS,'*','WHERE smiley_path = "'.$path.'"') > 0;
	}
	
	/**
	 * Checks wether the given smiley-code does already exist and doesn't belong to the smiley
	 * with given id
	 *
	 * @param string $code the smiley-code
	 * @param int $id the smiley-id
	 * @return bool true if the code exists
	 */
	public function code_exists($code,$id)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(
			BS_TB_SMILEYS,
			'*',
			'WHERE (primary_code = "'.$code.'" OR secondary_code = "'.$code.'") AND id != '.$id
		) > 0;
	}
	
	/**
	 * Returns the smiley with given id
	 *
	 * @param int $id the smiley-id
	 * @return array|bool the smiley-data or false if failed
	 */
	public function get_by_id($id)
	{
		$rows = $this->get_by_ids(array($id));
		if(count($rows) == 0)
			return false;
		
		return $rows[0];
	}
	
	/**
	 * Returns all smileys with given ids
	 *
	 * @param array $ids
	 * @return array the smileys
	 */
	public function get_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_SMILEYS.'
			 WHERE id IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns the next sort-key that should be used for new entries
	 *
	 * @return int the sort-key
	 */
	public function get_next_sort_key()
	{
		$db = FWS_Props::get()->db();

		$row = $db->get_row('SELECT MAX(sort_key) AS k FROM '.BS_TB_SMILEYS);
		if(!$row)
			return 1;
		
		return $row['k'] + 1;
	}
	
	/**
	 * Returns all smileys, optional in the given range.
	 * 
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all smileys sorted by sort_key ascending
	 */
	public function get_list($start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_SMILEYS.'
			 ORDER BY sort_key ASC
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Creates a new smiley with the given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_SMILEYS,$fields);
	}
	
	/**
	 * Updates the fields of the smiley with given id
	 *
	 * @param int $id the id
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_id($id,$fields)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_SMILEYS,'WHERE id = '.$id,$fields);
	}
	
	/**
	 * Updates the sort of the smiley with given id. You can push a smiley up or down
	 *
	 * @param int $id the smiley-id
	 * @param boolean $up do you want to push it up? (up = increase sort)
	 * @return int the number of affected rows
	 */
	public function update_sort($id,$up = true)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		$fields = array(
			'sort_key' => array('sort_key '.($up ? '+' : '-').' 1')
		);
		return $db->update(BS_TB_SMILEYS,'WHERE id = '.$id,$fields);
	}
	
	/**
	 * Deletes the smileys with given ids
	 *
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_SMILEYS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>