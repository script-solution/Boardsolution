<?php
/**
 * Contains the sessions-dao-class
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
 * The DAO-class for the sessions-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Sessions extends FWS_Singleton
{
	/**
	 * @return BS_DAO_Sessions the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Checks wether the given key which is the md5-hash of session-id and user-ip is equal
	 * to the one in the session-table of the given user
	 *
	 * @param int $user_id the user-id
	 * @param string $key the key
	 * @return boolean true if so
	 */
	public function check_sessionip_key($user_id,$key)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->get_row_count(
			BS_TB_SESSIONS,
			'user_id',
			' WHERE user_id = '.$user_id." AND MD5(CONCAT(session_id,user_ip)) = '".$key."'"
		) > 0;
	}
	
	/**
	 * Returns all entries sorted by date descending
	 *
	 * @return array the entries
	 */
	public function get_all()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT s.*,u.`'.BS_EXPORT_USER_NAME.'` user_name,p.ghost_mode,p.user_group
			 FROM '.BS_TB_SESSIONS.' s
			 LEFT JOIN '.BS_TB_USER.' u ON s.user_id = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON s.user_id = p.id
			 ORDER BY s.date DESC'
		);
	}
	
	/**
	 * Creates a new entry with the given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_SESSIONS,$fields);
	}
	
	/**
	 * Updates the entry with given session-id
	 *
	 * @param string $sid the session-id
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_sid($sid,$fields)
	{
		$db = FWS_Props::get()->db();

		return $db->update(BS_TB_SESSIONS,'WHERE session_id = "'.$sid.'"',$fields);
	}
	
	/**
	 * Deletes all rows with given session-ids
	 *
	 * @param array $sids the session-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_sids($sids)
	{
		$db = FWS_Props::get()->db();

		if(!is_array($sids) || count($sids) == 0)
			FWS_Helper::def_error('array>0','sids',$sids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_SESSIONS.' WHERE session_id IN ("'.implode('","',$sids).'")'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all rows with given user-ids
	 *
	 * @param array $ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_SESSIONS.' WHERE user_id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>