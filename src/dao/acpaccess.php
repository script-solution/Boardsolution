<?php
/**
 * Contains the acpaccess-dao-class
 * 
 * @package			Boardsolution
 * @subpackage	src.dao
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
class BS_DAO_ACPAccess extends FWS_Singleton
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
		$db = FWS_Props::get()->db();

		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(!in_array($type,array('user','group')))
			FWS_Helper::def_error('inarray','type',array('user','group'),$type);
		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->insert(BS_TB_ACP_ACCESS,array(
			'module' => $module,
			'access_type' => $type,
			'access_value' => $id
		));
	}
	
	/**
	 * Deletes the given type with given value
	 *
	 * @param string $type user or group
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	public function delete($type,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!in_array($type,array('user','group')))
			FWS_Helper::def_error('inarray','type',array('user','group'),$type);
		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_ACP_ACCESS.'
			 WHERE access_type = "'.$type.'" AND access_value IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries for the given module
	 *
	 * @param string $module the module-name
	 * @return int the number of affected rows
	 */
	public function delete_module($module)
	{
		$db = FWS_Props::get()->db();

		$db->execute(
			'DELETE FROM '.BS_TB_ACP_ACCESS.' WHERE module = "'.$module.'"'
		);
		return $db->get_affected_rows();
	}
}
?>