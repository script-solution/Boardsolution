<?php
/**
 * Contains the bbcodes-dao-class
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
 * The DAO-class for the bbcodes-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_BBCodes extends FWS_Singleton
{
	/**
	 * @return BS_DAO_BBCodes the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the total number of bbcodes
	 */
	public function get_count()
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_BBCODES,'*','');
	}
	
	/**
	 * @param string $keyword the keyword
	 * @return int the number of bbcodes which match the given keyword
	 */
	public function get_count_by_keyword($keyword)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_BBCODES,'*',
			' WHERE name LIKE "%'.$keyword.'%" OR type LIKE "%'.$keyword.'%" OR
			 		content LIKE "%'.$keyword.'%" OR replacement LIKE "%'.$keyword.'%" OR
			 		replacement_param LIKE "%'.$keyword.'%" OR param LIKE "%'.$keyword.'%" OR
					param_type LIKE "%'.$keyword.'%" OR allowed_content LIKE "%'.$keyword.'%"');
	}
	
	/**
	 * Checks wether a tag with the given name exists. Optional you can specify a tag-id which
	 * will be excluded from the check.
	 *
	 * @param string $name the tag-name
	 * @param int $id the bbcode-id (0 = ignore)
	 * @return bool true if it exists
	 */
	public function name_exists($name,$id = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id < 0)
			FWS_Helper::def_error('intge0','id',$id);
		
		return $db->get_row_count(
			BS_TB_BBCODES,'*','WHERE name = "'.$name.'"'.($id > 0 ? ' AND id != '.$id : '')
		) > 0;
	}
	
	/**
	 * @param int $id the bbcode-id
	 * @return array|bool the tag with given id or false if not found
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
	 * @param array $ids the ids
	 * @return array all found rows
	 */
	public function get_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_BBCODES.'
			 WHERE id IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns a list with all bbcode-tags
	 *
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array the bbcode-tags
	 */
	public function get_list($start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_BBCODES.'
			'.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns a list with all bbcode-tags which match the given keyword
	 *
	 * @param string $keyword the keyword
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array the bbcode-tags
	 */
	public function get_list_by_keyword($keyword,$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_BBCODES.'
			 WHERE
			 	name LIKE "%'.$keyword.'%" OR type LIKE "%'.$keyword.'%" OR
			 	content LIKE "%'.$keyword.'%" OR replacement LIKE "%'.$keyword.'%" OR
			 	replacement_param LIKE "%'.$keyword.'%" OR param LIKE "%'.$keyword.'%" OR
			 	param_type LIKE "%'.$keyword.'%" OR allowed_content LIKE "%'.$keyword.'%"
			'.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * @return array all contents that are currently used
	 */
	public function get_contents()
	{
		$db = FWS_Props::get()->db();

		$rows = $db->get_rows(
			'SELECT DISTINCT content FROM '.BS_TB_BBCODES.' ORDER BY content ASC'
		);
		$types = array();
		foreach($rows as $row)
			$types[] = $row['content'];
		return $types;
	}
	
	/**
	 * @return array all types that are currently used
	 */
	public function get_types()
	{
		$db = FWS_Props::get()->db();

		$rows = $db->get_rows(
			'SELECT DISTINCT type FROM '.BS_TB_BBCODES.' ORDER BY type ASC'
		);
		$types = array();
		foreach($rows as $row)
			$types[] = $row['type'];
		return $types;
	}
	
	/**
	 * Creates a new entry with given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_BBCODES,$fields);
	}
	
	/**
	 * Updates the given fields for the entry with given id
	 *
	 * @param int $id the tag-id
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_id($id,$fields)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_BBCODES,'WHERE id = '.$id,$fields);
	}
	
	/**
	 * Deletes all entries with given ids
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
			'DELETE FROM '.BS_TB_BBCODES.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>