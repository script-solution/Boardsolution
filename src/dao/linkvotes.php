<?php
/**
 * Contains the link-votes-dao-class
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
 * The DAO-class for the link-votes-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_LinkVotes extends FWS_Singleton
{
	/**
	 * @return BS_DAO_LinkVotes the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns all link-ids for which the given user has voted
	 *
	 * @param int $user_id the user-id
	 * @return array all link-ids
	 */
	public function get_votes_of_user($user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$rows = $db->get_rows(
			'SELECT link_id FROM '.BS_TB_LINK_VOTES.' WHERE user_id = '.$user_id
		);
		$lids = array();
		foreach($rows as $row)
			$lids[] = $row['link_id'];
		return $lids;
	}
	
	/**
	 * Votes with the given user for the given link
	 *
	 * @param int $link_id the link-id
	 * @param int $user_id the user-id
	 */
	public function vote($link_id,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($link_id) || $link_id <= 0)
			FWS_Helper::def_error('intgt0','link_id',$link_id);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$db->insert(BS_TB_LINK_VOTES,array(
			'link_id' => $link_id,
			'user_id' => $user_id
		));
	}
	
	/**
	 * Deletes all entries that belong to links with given ids
	 *
	 * @param array $ids the link-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_links($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_LINK_VOTES.' WHERE link_id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>