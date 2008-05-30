<?php
/**
 * Contains the attachments-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the attachment-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Attachments extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Attachments the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the number of attachments
	 */
	public function get_attachment_count()
	{
		return $this->db->sql_num(BS_TB_ATTACHMENTS,'id','');
	}
	
	/**
	 * @param int $id the user-id
	 * @return int the number of attachments of the given user
	 */
	public function get_attachment_count_of_user($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		return $this->db->sql_num(BS_TB_ATTACHMENTS,'id',' WHERE poster_id = '.$id);
	}
	
	/**
	 * Returns the number of occurrences of the given path
	 *
	 * @param string $path the attachment-path
	 * @return int the number of attachments
	 */
	public function get_attachment_count_of_path($path)
	{
		return $this->db->sql_num(
			BS_TB_ATTACHMENTS,'id',' WHERE attachment_path = "'.$path.'"'
		);
	}
	
	/**
	 * @return array an array with all attachments
	 */
	public function get_all()
	{
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_ATTACHMENTS
		);
	}
	
	/**
	 * Returns all attachments including the user-name and topic-name
	 *
	 * @return array the attachments
	 */
	public function get_all_with_names()
	{
		return $this->db->sql_rows(
			'SELECT a.*,t.name,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_ATTACHMENTS.' a
			 LEFT JOIN '.BS_TB_USER.' u ON a.poster_id = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_THREADS.' t ON a.thread_id = t.id'
		);
	}
	
	/**
	 * Returns the attachment with the given id
	 *
	 * @param int $id the attachment-id
	 * @return array the data of it
	 */
	public function get_by_id($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		return $this->db->sql_fetch(
			'SELECT *
			 FROM '.BS_TB_ATTACHMENTS.'
			 WHERE id = '.$id
		);
	}
	
	/**
	 * Returns the attachment with given path of the user with given id.
	 * That means you can be sure that the user is allowed to view this attachment.
	 *
	 * @param string $path the attachment-path
	 * @param int $user_id user-id
	 * @return array the attachment
	 */
	public function get_attachment_of_user_by_path($path,$user_id)
	{
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		return $this->db->sql_fetch(
			'SELECT *
			 FROM '.BS_TB_ATTACHMENTS.'
			 WHERE attachment_path = "'.$path.'" AND IF(pm_id > 0,poster_id = '.$user_id.',1)'
		);
	}
	
	/**
	 * Returns all attachments with the given paths
	 *
	 * @param array $paths an array with all paths
	 * @return array the attachments
	 */
	public function get_by_paths($paths)
	{
		if(!is_array($paths) || count($paths) == 0)
			PLIB_Helper::def_error('array>0','paths',$paths);
		
		return $this->db->sql_rows(
			'SELECT *
			 FROM '.BS_TB_ATTACHMENTS.'
			 WHERE attachment_path IN ("'.implode('","',$paths).'")'
		);
	}
	
	/**
	 * Returns all attachments with the given topic-ids
	 *
	 * @param array $tids an array with all topic-ids
	 * @return array the attachments
	 */
	public function get_by_topicids($tids)
	{
		return $this->_get_by('thread_id',$tids);
	}
	
	/**
	 * Returns the attachment with the given post-id
	 *
	 * @param int $pid the post-id
	 * @return array the attachment
	 */
	public function get_by_postid($pid)
	{
		return $this->_get_by('post_id',array($pid));
	}
	
	/**
	 * Returns all attachments with the given post-ids
	 *
	 * @param array $pids an array with all post-ids
	 * @return array the attachments
	 */
	public function get_by_postids($pids)
	{
		return $this->_get_by('post_id',$pids);
	}
	
	/**
	 * Returns the attachment with the given PM-id
	 *
	 * @param int $pmid the PM-id
	 * @return array the attachment
	 */
	public function get_by_pmid($pmid)
	{
		return $this->_get_by('pm_id',array($pmid));
	}
	
	/**
	 * Returns all attachments with the given PM-ids
	 *
	 * @param array $pmids an array with all PM-ids
	 * @return array the attachments
	 */
	public function get_by_pmids($pmids)
	{
		return $this->_get_by('pm_id',$pmids);
	}
	
	/**
	 * Creates an attachment with the given values
	 *
	 * @param int $post_id the post-id (0 if for a PM)
	 * @param int $topic_id the topic-id (0 if for a PM)
	 * @param int $pm_id the PM-id (0 if for a post)
	 * @param int $user_id the user-id
	 * @param int $size the attachment-size
	 * @param string $path the attachment-path
	 * @return int the used id
	 */
	public function create($post_id,$topic_id,$pm_id,$user_id,$size,$path)
	{
		if(!PLIB_Helper::is_integer($post_id) || $post_id < 0)
			PLIB_Helper::def_error('intge0','post_id',$post_id);
		if(!PLIB_Helper::is_integer($topic_id) || $topic_id < 0)
			PLIB_Helper::def_error('intge0','topic_id',$topic_id);
		if(!PLIB_Helper::is_integer($pm_id) || $pm_id < 0)
			PLIB_Helper::def_error('intge0','pm_id',$pm_id);
		if(!PLIB_Helper::is_integer($user_id) || $user_id < 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		if(!PLIB_Helper::is_integer($size) || $size <= 0)
			PLIB_Helper::def_error('intge0','size',$size);
		if(empty($path))
			PLIB_Helper::def_error('notempty','path',$path);
		
		$this->db->sql_insert(BS_TB_ATTACHMENTS,array(
			'post_id' => $post_id,
			'thread_id' => $topic_id,
			'pm_id' => $pm_id,
			'poster_id' => $user_id,
			'attachment_size' => $size,
			'attachment_path' => $path
		));
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Increments the downloads of the attachment with given id by 1.
	 *
	 * @param int $id the id
	 * @return int the number of affected rows
	 */
	public function inc_downloads($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_update(BS_TB_ATTACHMENTS,'WHERE id = '.$id,array(
			'downloads' => array('downloads + 1')
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Updates the topic-id to given one for all given post-ids
	 *
	 * @param array $post_ids the post-ids
	 * @param int $topic_id the new topic-id
	 * @return int the number of affected rows
	 */
	public function update_topic_id($post_ids,$topic_id)
	{
		if(!PLIB_Array_Utils::is_integer($post_ids) || count($post_ids) == 0)
			PLIB_Helper::def_error('intarray>0','post_ids',$post_ids);
		if(!PLIB_Helper::is_integer($topic_id) || $topic_id <= 0)
			PLIB_Helper::def_error('intgt0','topic_id',$topic_id);
		
		$this->db->sql_update(BS_TB_ATTACHMENTS,'WHERE post_id IN ('.implode(',',$post_ids).')',array(
			'thread_id' => $topic_id
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all attachments with given ids
	 *
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		return $this->_delete_by('id',$ids);
	}
	
	/**
	 * Deletes all attachments with given topic-ids
	 *
	 * @param array $tids the topic-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_topicids($tids)
	{
		return $this->_delete_by('thread_id',$tids);
	}
	
	/**
	 * Deletes all attachments with given post-ids
	 *
	 * @param array $pids the post-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_postids($pids)
	{
		return $this->_delete_by('post_id',$pids);
	}
	
	/**
	 * Deletes all attachments with given PM-ids
	 *
	 * @param array $pmids the PM-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_pmids($pmids)
	{
		return $this->_delete_by('pm_id',$pmids);
	}
	
	/**
	 * Deletes all PM-attachments of the given users
	 *
	 * @param array $ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_pm_attachments_of_users($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_ATTACHMENTS.'
			 WHERE poster_id IN ('.implode(',',$ids).') AND pm_id > 0'
		);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Returns all attachments for which the given field contains one of the given ids
	 *
	 * @param string $field the field to use
	 * @param array $ids an array with all ids
	 * @return array the attachments
	 */
	protected function _get_by($field,$ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		return $this->db->sql_rows(
			'SELECT *
			 FROM '.BS_TB_ATTACHMENTS.'
			 WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Deletes attachments by the given field and ids
	 *
	 * @param string $field the field to use
	 * @param array $ids an array with all ids
	 * @return int the number of affected rows
	 */
	protected function _delete_by($field,$ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_ATTACHMENTS.' WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
}
?>