<?php
/**
 * Contains the poll-votes-dao-class
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
 * The DAO-class for the poll-votes-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_PollVotes extends FWS_Singleton
{
	/**
	 * @return BS_DAO_ChangePW the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Checks wether the given user has already voted for the given poll
	 *
	 * @param int $poll_id the poll-id
	 * @param int $user_id the user-id
	 * @return boolean true if so
	 */
	public function user_voted($poll_id,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($poll_id) || $poll_id <= 0)
			FWS_Helper::def_error('intgt0','poll_id',$poll_id);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->get_row_count(
			BS_TB_POLL_VOTES,'user_id',' WHERE poll_id = '.$poll_id.' AND user_id = '.$user_id
		) > 0;
	}
	
	/**
	 * Creates a new entry
	 *
	 * @param int $poll_id the poll-id
	 * @param int $user_id the user-id
	 * @return int the used id
	 */
	public function create($poll_id,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($poll_id) || $poll_id <= 0)
			FWS_Helper::def_error('intgt0','poll_id',$poll_id);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->insert(BS_TB_POLL_VOTES,array(
			'poll_id' => $poll_id,
			'user_id' => $user_id
		));
	}
	
	/**
	 * Deletes all entries for the given polls
	 *
	 * @param array $ids the poll-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_polls($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_POLL_VOTES.' WHERE poll_id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>