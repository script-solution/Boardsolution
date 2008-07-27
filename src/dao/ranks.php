<?php
/**
 * Contains the ranks-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the ranks-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Ranks extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Ranks the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Creates a new (empty) rank
	 *
	 * @return int the used id
	 */
	public function create()
	{
		$db = PLIB_Props::get()->db();

		$db->sql_insert(BS_TB_RANKS,array(
			'rank' => ''
		));
		return $db->get_last_insert_id();
	}
	
	/**
	 * Updates the given fields for the rank with given id
	 *
	 * @param int $id the rank-id
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_id($id,$fields)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$db->sql_update(BS_TB_RANKS,'WHERE id = '.$id,$fields);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all ranks with given ids
	 *
	 * @param array $ids the ids to delete
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_RANKS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>