<?php
/**
 * Contains the acpaccess-dao-class
 *
 * @version			$Id: acpaccess.php 737 2008-05-23 18:26:46Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the acp-access-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_ACPAccess extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Profile the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns all entries of the acp-access-table. Additionally you get the corresponding
	 * username (NULL if it is a group-entry)
	 *
	 * @return array all rows
	 */
	public function get_all()
	{
		return $this->db->sql_rows(
			'SELECT a.*,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_ACP_ACCESS.' a
			 LEFT JOIN '.BS_TB_USER.' u ON u.`'.BS_EXPORT_USER_ID.'` = a.access_value'
		);
	}
	
	/**
	 * Returns all entries of the acp-access-table that belong to the given module.
	 * Additionally you get the corresponding username (NULL if it is a group-entry)
	 *
	 * @return array all found rows
	 */
	public function get_by_module($module)
	{
		return $this->db->sql_rows(
			'SELECT a.*,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_ACP_ACCESS.' a
			 LEFT JOIN '.BS_TB_USER.' u ON u.`'.BS_EXPORT_USER_ID.'` = a.access_value
			 WHERE a.module = "'.$module.'"'
		);
	}
	
	/**
	 * Creates a new entry with the given module, type and id
	 *
	 * @param string $module the module-name
	 * @param string $type user or group
	 * @param int $id the id
	 * @return int the used id
	 */
	public function create($module,$type,$id)
	{
		if(!in_array($type,array('user','group')))
			PLIB_Helper::def_error('inarray','type',array('user','group'),$type);
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_insert(BS_TB_ACP_ACCESS,array(
			'module' => $module,
			'access_type' => $type,
			'access_value' => $id
		));
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Deletes the given type with given value
	 *
	 * @param string $type user or group
	 * @param int $id the id
	 * @return int the number of affected rows
	 */
	public function delete($type,$ids)
	{
		if(!in_array($type,array('user','group')))
			PLIB_Helper::def_error('inarray','type',array('user','group'),$type);
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_ACP_ACCESS.'
			 WHERE access_type = "'.$type.'" AND access_value IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries for the given module
	 *
	 * @param string $module the module-name
	 * @return int the number of affected rows
	 */
	public function delete_module($module)
	{
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_ACP_ACCESS.' WHERE module = "'.$module.'"'
		);
		return $this->db->get_affected_rows();
	}
}
?>