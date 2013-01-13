<?php
/**
 * Contains the intern-dao-class
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
class BS_DAO_Intern extends FWS_Singleton
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
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		
		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!in_array($type,array('user','group')))
			FWS_Helper::def_error('inarray','type',array('user','group'),$type);
		if(!FWS_Helper::is_integer($value) || $value <= 0)
			FWS_Helper::def_error('intgt0','value',$value);
		
		return $db->insert(BS_TB_INTERN,array(
			'fid' => $fid,
			'access_type' => $type,
			'access_value' => $value
		));
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_INTERN.' WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>