<?php
/**
 * Contains the poll-votes-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
class BS_DAO_PollVotes extends PLIB_Singleton
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
		if(!PLIB_Helper::is_integer($poll_id) || $poll_id <= 0)
			PLIB_Helper::def_error('intgt0','poll_id',$$poll_id);
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		return $this->db->sql_num(
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
		if(!PLIB_Helper::is_integer($poll_id) || $poll_id <= 0)
			PLIB_Helper::def_error('intgt0','poll_id',$$poll_id);
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		$this->db->sql_insert(BS_TB_POLL_VOTES,array(
			'poll_id' => $poll_id,
			'user_id' => $user_id
		));
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Deletes all entries for the given polls
	 *
	 * @param array $ids the poll-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_polls($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_POLL_VOTES.' WHERE poll_id IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
}
?>