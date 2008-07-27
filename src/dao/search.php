<?php
/**
 * Contains the search-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the search-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Search extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Search the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns the entry with given id
	 *
	 * @param string $id the search-id
	 * @return array the data or false if not found
	 */
	public function get_by_id($id)
	{
		$db = PLIB_Props::get()->db();

		$row = $db->sql_fetch(
			'SELECT * FROM '.BS_TB_SEARCH.' WHERE id = "'.$id.'"'
		);
		if(!$row)
			return false;
		return $row;
	}
	
	/**
	 * Creates a new entry with given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the id that has been used
	 */
	public function create($fields)
	{
		$db = PLIB_Props::get()->db();

		$db->sql_insert(BS_TB_SEARCH,$fields);
		return $db->get_last_insert_id();
	}
	
	/**
	 * Deletes all "timed out" searches.
	 *
	 * @param int $timeout the timeout in seconds
	 * @return int the number of affected rows
	 */
	public function delete_timedout($timeout)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($timeout) || $timeout <= 0)
			PLIB_Helper::def_error('intgt0','timeout',$timeout);
		
		$db->sql_qry('DELETE FROM '.BS_TB_SEARCH.' WHERE search_date < '.(time() - $timeout));
		return $db->get_affected_rows();
	}
}
?>