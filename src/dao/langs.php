<?php
/**
 * Contains the langs-dao-class
 *
 * @version			$Id: langs.php 796 2008-05-29 18:23:27Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the langs-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Langs extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Langs the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Creates a new (empty) entry
	 *
	 * @return int the used id
	 */
	public function create()
	{
		$this->db->sql_insert(BS_TB_LANGS,array(
			'lang_name' => '',
			'lang_folder' => ''
		));
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Updates the entry with given id
	 *
	 * @param int $id the language-id
	 * @param string $name the new name
	 * @param string $folder the new folder
	 * @return int the number of affected rows
	 */
	public function update_by_id($id,$name,$folder)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_update(BS_TB_LANGS,'WHERE id = '.$id,array(
			'lang_name' => $name,
			'lang_folder' => $folder
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes the entries with given ids
	 *
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_LANGS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
}
?>