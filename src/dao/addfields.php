<?php
/**
 * Contains the addfields-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
class BS_DAO_AddFields extends PLIB_Singleton
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
	 * @return array the row or false if not found
	 */
	public function get_by_id($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$row = $this->db->sql_fetch(
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
		$this->db->sql_insert(BS_TB_USER_FIELDS,$fields);
		return $this->db->get_last_insert_id();
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
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_update(BS_TB_USER_FIELDS,'WHERE id = '.$id,$fields);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Decrements the sort by 1 for all fields which sort is greater than <var>$lower</var>
	 *
	 * @param int $lower the lower value
	 * @return int the number of affected rows
	 */
	public function dec_sort($lower)
	{
		$this->db->sql_update(BS_TB_USER_FIELDS,'WHERE field_sort > '.$lower,array(
			'field_sort' => array('field_sort - 1')
		));
		return $this->db->get_affected_rows();
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
		$this->db->sql_update(BS_TB_USER_FIELDS,'WHERE id = '.$id,array(
			'field_sort' => $sort
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes the entry with given id
	 *
	 * @param int $id the id
	 * @return int the number of affected rows
	 */
	public function delete($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_USER_FIELDS.' WHERE id = '.$id
		);
		return $this->db->get_affected_rows();
	}
}
?>