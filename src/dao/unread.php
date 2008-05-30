<?php
/**
 * Contains the unread-dao-class
 *
 * @version			$Id: unread.php 796 2008-05-29 18:23:27Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the unread-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Unread extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Unread the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns all unread rows with given ids for the given type
	 *
	 * @param string $type the type: rubrikid or threadid
	 * @param array $ids the ids for the type
	 * @return array all rows
	 */
	public function get_all_by_type($type,$ids)
	{
		if(!in_array($type,array('rubrikid','threadid')))
			PLIB_Helper::def_error('inarray','type',array('rubrikid','threadid'),$type);
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		return $this->db->sql_rows(
			'SELECT u.* FROM '.BS_TB_UNREAD.' u
			 LEFT JOIN '.BS_TB_POSTS.' p ON u.post_id = p.id
			 WHERE p.'.$type.' IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns all unread rows for the given user
	 *
	 * @param int $id the user-id
	 * @return array all rows
	 */
	public function get_all_of_user($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		return $this->db->sql_rows(
			'SELECT u.*,p.rubrikid,p.threadid FROM '.BS_TB_UNREAD.' u
			 LEFT JOIN '.BS_TB_POSTS.' p ON u.post_id = p.id
			 WHERE u.user_id = '.$id
		);
	}
	
	/**
	 * Create new entries for the given user
	 *
	 * @param int $userid the user-id
	 * @param array $postids an array with the post-ids to create:
	 * 	<code>array(<postID> => <isNews>,...)</code>
	 */
	public function create($userid,$postids)
	{
		if(!PLIB_Helper::is_integer($userid) || $userid <= 0)
			PLIB_Helper::def_error('intgt0','userid',$userid);
		if(!is_array($postids) || count($postids) == 0)
			PLIB_Helper::def_error('array>0','postids',$postids);
		
		$sql = 'INSERT INTO '.BS_TB_UNREAD.' (post_id,user_id,is_news) VALUES ';
		$i = 0;
		$len = count($postids);
		foreach($postids as $pid => $is_news)
		{
			$sql .= '('.$pid.','.$userid.','.($is_news ? '1' : '0').')';
			if($i++ < $len - 1)
				$sql .= ',';
		}
		$this->db->sql_qry($sql);
	}
	
	/**
	 * Updates the post-id for the given post-id
	 *
	 * @param int $post_id the current post-id
	 * @param int $new_post_id the new post-id
	 * @return int the number of affected rows
	 */
	public function update_by_post($post_id,$new_post_id)
	{
		if(!PLIB_Helper::is_integer($post_id) || $post_id <= 0)
			PLIB_Helper::def_error('intgt0','post_id',$post_id);
		if(!PLIB_Helper::is_integer($new_post_id) || $new_post_id <= 0)
			PLIB_Helper::def_error('intgt0','new_post_id',$new_post_id);
		
		$this->db->sql_update(BS_TB_UNREAD,'WHERE post_id = '.$post_id,array(
			'post_id' => $new_post_id
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all rows for the given user
	 *
	 * @param int $id the used-id
	 * @return int the number of affected rows
	 */
	public function delete_by_user($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_UNREAD.' WHERE user_id = '.$id
		);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all rows with the given posts
	 *
	 * @param array $post_ids the post-ids
	 * @return int the number of affected rows
	 */
	public function delete_posts($post_ids)
	{
		if(!PLIB_Array_Utils::is_integer($post_ids) || count($post_ids) == 0)
			PLIB_Helper::def_error('intarray>0','post_ids',$post_ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_UNREAD.' WHERE post_id IN ('.implode(',',$post_ids).')'
		);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all posts with given ids for the given user
	 *
	 * @param int $id the used-id
	 * @param array $post_ids the post-ids
	 * @return int the number of affected rows
	 */
	public function delete_posts_of_user($id,$post_ids)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		if(!PLIB_Array_Utils::is_integer($post_ids) || count($post_ids) == 0)
			PLIB_Helper::def_error('intarray>0','post_ids',$post_ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_UNREAD.'
			 WHERE user_id = '.$id.' AND post_id IN ('.implode(',',$post_ids).')'
		);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all news for the given user
	 *
	 * @param int $id the used-id
	 * @return int the number of affected rows
	 */
	public function delete_news_of_user($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_UNREAD.'
			 WHERE user_id = '.$id.' AND is_news = 1'
		);
		return $this->db->get_affected_rows();
	}
}
?>