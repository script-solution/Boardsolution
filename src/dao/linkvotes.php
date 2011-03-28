<?php
/**
 * Contains the link-votes-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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