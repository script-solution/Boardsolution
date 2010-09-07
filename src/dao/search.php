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
class BS_DAO_Search extends FWS_Singleton
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
	 * @param int $id the search-id
	 * @return array|bool the data or false if not found
	 */
	public function get_by_id($id)
	{
		$db = FWS_Props::get()->db();

		$row = $db->get_row(
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
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_SEARCH,$fields);
	}
	
	/**
	 * Deletes all "timed out" searches.
	 *
	 * @param int $timeout the timeout in seconds
	 * @return int the number of affected rows
	 */
	public function delete_timedout($timeout)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($timeout) || $timeout <= 0)
			FWS_Helper::def_error('intgt0','timeout',$timeout);
		
		$db->execute('DELETE FROM '.BS_TB_SEARCH.' WHERE search_date < '.(time() - $timeout));
		return $db->get_affected_rows();
	}
}
?>