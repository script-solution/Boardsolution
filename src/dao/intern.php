<?php
/**
 * Contains the intern-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the intern-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Intern extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Intern the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns all entries for the given forum
	 *
	 * @param int $fid the forum-id
	 * @return array the entries for the forum
	 */
	public function get_by_forum($fid)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($fid) || $fid <= 0)
			PLIB_Helper::def_error('intgt0','fid',$fid);
		
		return $db->sql_rows(
			'SELECT i.*,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_INTERN.' i
			 LEFT JOIN '.BS_TB_USER.' u ON i.access_value = u.`'.BS_EXPORT_USER_ID.'`
			 WHERE i.fid = '.$fid
		);
	}
	
	/**
	 * Creates a new entry
	 *
	 * @param int $fid the forum-id
	 * @param string $type the type: user or group
	 * @param int $value the value (user-id or group-id)
	 * @return int the used id
	 */
	public function create($fid,$type,$value)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Helper::is_integer($fid) || $fid <= 0)
			PLIB_Helper::def_error('intgt0','fid',$fid);
		if(!in_array($type,array('user','group')))
			PLIB_Helper::def_error('inarray','type',array('user','group'),$type);
		if(!PLIB_Helper::is_integer($value) || $value <= 0)
			PLIB_Helper::def_error('intgt0','value',$value);
		
		$db->sql_insert(BS_TB_INTERN,array(
			'fid' => $fid,
			'access_type' => $type,
			'access_value' => $value
		));
		return $db->get_last_insert_id();
	}
	
	/**
	 * Deletes all entries for the given users
	 *
	 * @param array $ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($ids)
	{
		return $this->delete_by_type('user',$ids);
	}
	
	/**
	 * Deletes all entries for the given groups
	 *
	 * @param array $gids the group-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_groups($gids)
	{
		return $this->delete_by_type('group',$gids);
	}
	
	/**
	 * Deletes all entries with given ids
	 *
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		return $this->delete_by('id',$ids);
	}
	
	/**
	 * Deletes all entries for the given forums
	 *
	 * @param array $fids the forum-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_forums($fids)
	{
		return $this->delete_by('fid',$fids);
	}
	
	/**
	 * Deletes entries for the given access-type
	 *
	 * @param string $type the type: user or group
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	protected function delete_by_type($type,$ids)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_INTERN.'
			 WHERE access_type = "'.$type.'" AND access_value IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes entries with given ids
	 *
	 * @param string $field the field-name
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	protected function delete_by($field,$ids)
	{
		$db = PLIB_Props::get()->db();

		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_INTERN.' WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>