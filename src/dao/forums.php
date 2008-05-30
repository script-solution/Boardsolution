<?php
/**
 * Contains the forums-dao-class
 *
 * @version			$Id: forums.php 796 2008-05-29 18:23:27Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the forums-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Forums extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Forums the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the total number of forums
	 */
	public function get_count()
	{
		return $this->db->sql_num(BS_TB_FORUMS,'id','');
	}
	
	/**
	 * Returns all forums. You can specify the sort and range.
	 *
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of avatars (for the LIMIT-statement). 0 = unlimited
	 * @return array the forums
	 */
	public function get_all($sort = 'id',$order = 'ASC',$start = 0,$count = 0)
	{
		if(!PLIB_Helper::is_integer($start) || $start < 0)
			PLIB_Helper::def_error('intge0','start',$start);
		if(!PLIB_Helper::is_integer($count) || $count < 0)
			PLIB_Helper::def_error('intge0','count',$count);
		
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_FORUMS.'
			 ORDER BY '.$sort.' '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns all forums for the forum-cache. That means you'll get all fields of the forum-table
	 * and some fields from other tables that will be joined.
	 *
	 * @return array the forums
	 */
	public function get_all_for_cache()
	{
		return $this->db->sql_rows(
			'SELECT f.*,b.post_user lastpost_userid,b.post_time lastpost_time,
							b.post_an_user lastpost_an_user,b.threadid lastpost_topicid,
							u.`'.BS_EXPORT_USER_NAME.'` lastpost_username,t.posts lastpost_topicposts,
							t.name lastpost_topicname,p.user_group lastpost_usergroups
			 FROM '.BS_TB_FORUMS.' f
			 LEFT JOIN '.BS_TB_POSTS.' b ON f.lastpost_id = b.id
			 LEFT JOIN '.BS_TB_USER.' u ON b.post_user = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON b.post_user = p.id
			 LEFT JOIN '.BS_TB_THREADS.' t ON b.threadid = t.id
			 ORDER BY f.parent_id ASC,f.sortierung ASC'
		);
	}
	
	/**
	 * Creates a new forum with given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$this->db->sql_insert(BS_TB_FORUMS,$fields);
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Updates the sort of the forum with given id
	 *
	 * @param int $id the forum-id
	 * @param mixed $sort the sort-value. May be an array, too (for "sortierung + 1" or similar)
	 * @return int the number of affected rows
	 */
	public function update_sort($id,$sort)
	{
		return $this->update_by_ids(array($id),array('sortierung' => $sort));
	}
	
	/**
	 * Updates the given fields for the forum with given id
	 *
	 * @param int $id the forum-id
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_id($id,$fields)
	{
		return $this->update_by_ids(array($id),$fields);
	}
	
	/**
	 * Updates the given fields for the forums with given ids
	 *
	 * @param array $ids the forum-ids
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_ids($ids,$fields)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_update(BS_TB_FORUMS,'WHERE id IN ('.implode(',',$ids).')',$fields);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Resets the attributes (threads, posts, lastpost_id) of the forums with given ids
	 *
	 * @param array $ids the forum-ids
	 * @return int the number of affected rows
	 */
	public function reset_attributes($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_update(BS_TB_FORUMS,'WHERE id IN ('.implode(',',$ids).')',array(
			'threads' => 0,
			'posts' => 0,
			'lastpost_id' => 0
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes the forums with given ids
	 *
	 * @param array $ids the forum-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry('DELETE FROM '.BS_TB_FORUMS.' WHERE id IN ('.implode(',',$ids).')');
		return $this->db->get_affected_rows();
	}
}
?>