<?php
/**
 * Contains the themes-dao-class
 *
 * @version			$Id: themes.php 796 2008-05-29 18:23:27Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the themes-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Themes extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Themes the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns wether the theme with given folder exists
	 *
	 * @param string $folder the theme-folder
	 * @return boolean true if it exists
	 */
	public function theme_exists($folder)
	{
		return $this->db->sql_num(BS_TB_THEMES,'id',' WHERE theme_folder = "'.$folder.'"') > 0;
	}
	
	/**
	 * Creates a new theme with given name and folder
	 *
	 * @param string $name the theme-name
	 * @param string $folder the theme-folder
	 * @return int the used id
	 */
	public function create($name,$folder)
	{
		if(empty($name))
			PLIB_Helper::def_error('notempty','name',$name);
		if(empty($folder))
			PLIB_Helper::def_error('notempty','folder',$folder);
		
		$this->db->sql_insert(BS_TB_THEMES,array(
			'theme_name' => $name,
			'theme_folder' => $folder
		));
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Updates the theme with given id
	 *
	 * @param int $id the theme-id
	 * @param string $name the theme-name
	 * @param string $folder the theme-folder
	 * @return int the number of affected rows
	 */
	public function update_by_id($id,$name,$folder = '')
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		if(empty($name))
			PLIB_Helper::def_error('notempty','name',$name);
		
		$fields = array(
			'theme_name' => $name,
		);
		if($folder)
			$fields['theme_folder'] = $folder;
		$this->db->sql_update(BS_TB_THEMES,'WHERE id = '.$id,$fields);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all themes with given ids
	 *
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_THEMES.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
}
?>