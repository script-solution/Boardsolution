<?php
/**
 * Contains the langs-dao-class
 *
 * @version			$Id$
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
class BS_DAO_Langs extends FWS_Singleton
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
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_LANGS,array(
			'lang_name' => '',
			'lang_folder' => ''
		));
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
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_LANGS,'WHERE id = '.$id,array(
			'lang_name' => $name,
			'lang_folder' => $folder
		));
	}
	
	/**
	 * Deletes the entries with given ids
	 *
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_LANGS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>