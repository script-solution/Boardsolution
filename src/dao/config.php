<?php
/**
 * Contains the config-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the config-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Config extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Config the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return array all config-entries sorted by group-id and sort
	 */
	public function get_all()
	{
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_CONFIG.' ORDER BY group_id ASC,sort ASC'
		);
	}
	
	/**
	 * Returns all config-entries which belong to groups that are child-groups of the given group-id
	 *
	 * @param int $group_id the group-id
	 * @return array the config-entries
	 */
	public function get_by_group($group_id)
	{
		if(!PLIB_Helper::is_integer($group_id) || $group_id <= 0)
			PLIB_Helper::def_error('intgt0','group_id',$group_id);
		
		return $this->db->sql_rows(
			'SELECT c.*
			 FROM '.BS_TB_CONFIG.' c
			 LEFT JOIN '.BS_TB_CONFIG_GROUPS.' g ON c.group_id = g.id
			 WHERE g.parent_id = '.$group_id.'
			 ORDER BY g.sort ASC,c.sort ASC'
		);
	}
	
	/**
	 * Updates the setting-value with given id
	 *
	 * @param int $id the setting-id
	 * @param mixed $value the new value
	 * @return int the number of affected rows
	 */
	public function update_setting($id,$value)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_update(BS_TB_CONFIG,'WHERE id = '.$id,array(
			'value' => $value
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Reverts the setting with given id
	 *
	 * @param int $id the setting-id
	 * @return int the number of affected rows
	 */
	public function revert_setting($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_update(BS_TB_CONFIG,'WHERE id = '.$id,array('value' => array('`default`')));
		return $this->db->get_affected_rows();
	}
}
?>