<?php
/**
 * Contains the cfg-groups-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the cfg-groups-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_CFGGroups extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_CFGGroups the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return array an array with all groups, sorted by "sort" ascending
	 */
	public function get_all()
	{
		$db = PLIB_Props::get()->db();

		return $db->sql_rows(
			'SELECT * FROM '.BS_TB_CONFIG_GROUPS.' ORDER BY sort ASC'
		);
	}
}
?>