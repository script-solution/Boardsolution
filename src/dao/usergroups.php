<?php
/**
 * Contains the user-groups-dao-class
 *
 * @version			$Id: usergroups.php 796 2008-05-29 18:23:27Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
class BS_DAO_UserGroups extends PLIB_Singleton
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
	 * @return array the data of the group or false if not found
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
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		return $this->db->sql_rows(
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
		$this->db->sql_insert(BS_TB_USER_GROUPS,$fields);
		return $this->db->get_last_insert_id();
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
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_update(BS_TB_USER_GROUPS,'WHERE id = '.$id,$fields);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes the groups with given ids
	 *
	 * @param array $ids the group-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_USER_GROUPS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
}
?>