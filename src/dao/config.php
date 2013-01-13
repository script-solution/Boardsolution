<?php
/**
 * Contains the config-dao-class
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
class BS_DAO_Config extends FWS_Singleton
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
		$db = FWS_Props::get()->db();

		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($group_id) || $group_id <= 0)
			FWS_Helper::def_error('intgt0','group_id',$group_id);
		
		return $db->get_rows(
			'SELECT c.*
			 FROM '.BS_TB_CONFIG.' c
			 LEFT JOIN '.BS_TB_CONFIG_GROUPS.' g ON c.group_id = g.id
			 WHERE g.parent_id = '.$group_id.'
			 ORDER BY g.sort ASC,c.sort ASC'
		);
	}
	
	/**
	 * Updates the setting-value with given name
	 *
	 * @param string $name the setting-name
	 * @param mixed $value the new value
	 * @return int the number of affected rows
	 */
	public function update_setting_by_name($name,$value)
	{
		$db = FWS_Props::get()->db();
		
		if(empty($name))
			FWS_Helper::def_error('notempty','name',$name);
		
		return $db->update(BS_TB_CONFIG,'WHERE name = "'.$name.'"',array(
			'value' => $value
		));
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
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_CONFIG,'WHERE id = '.$id,array(
			'value' => $value
		));
	}
	
	/**
	 * Reverts the setting with given id
	 *
	 * @param int $id the setting-id
	 * @return int the number of affected rows
	 */
	public function revert_setting($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_CONFIG,'WHERE id = '.$id,array('value' => array('`default`')));
	}
}
?>