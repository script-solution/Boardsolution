<?php
/**
 * Contains the topics-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the topics-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Topics extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Topics the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the total number of topics
	 */
	public function get_count()
	{
		return $this->db->sql_num(BS_TB_THREADS,'id','');
	}
	
	/**
	 * @param int $fid the forum-id
	 * @return int the number of topics in the given forum
	 */
	public function get_count_in_forum($fid)
	{
		if(!PLIB_Helper::is_integer($fid) || $fid <= 0)
			PLIB_Helper::def_error('intgt0','fid',$fid);
		
		return $this->db->sql_num(BS_TB_THREADS,'id',' WHERE rubrikid = '.$fid);
	}
	
	/**
	 * Returns the number of topics found by the given WHERE-clause. You can assume
	 * that the topic-table is named "t".
	 *
	 * @param string $where the WHERE-clause
	 * @return int the number of found topics
	 */
	public function get_count_by_search($where)
	{
		return $this->db->sql_num(BS_TB_THREADS.' t','t.id',$where);
	}
	
	/**
	 * Returns all topics in the given range.
	 *
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all found topics
	 */
	public function get_all($start = 0,$count = 0)
	{
		if(!PLIB_Helper::is_integer($start) || $start < 0)
			PLIB_Helper::def_error('intge0','start',$start);
		if(!PLIB_Helper::is_integer($count) || $count < 0)
			PLIB_Helper::def_error('intge0','count',$count);
		
		return $this->db->sql_rows(
			'SELECT id FROM '.BS_TB_THREADS.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns all topics that match the given WHERE-clause
	 *
	 * @param string $where the WHERE-clause
	 * @param string $order the value for the ORDER-BY-statement
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all found topics
	 */
	public function get_all_by_search($where,$order = 't.id ASC',$start = 0,$count = 0)
	{
		if(!PLIB_Helper::is_integer($start) || $start < 0)
			PLIB_Helper::def_error('intge0','start',$start);
		if(!PLIB_Helper::is_integer($count) || $count < 0)
			PLIB_Helper::def_error('intge0','count',$count);
		
		return $this->db->sql_rows(
			'SELECT t.*,u.`'.BS_EXPORT_USER_NAME.'` username,
							u2.`'.BS_EXPORT_USER_NAME.'` lp_username,rt.forum_name rubrikname,
							p.user_group,p2.user_group lastpost_user_group
			 FROM '.BS_TB_THREADS.' t
			 LEFT JOIN '.BS_TB_USER.' u ON t.post_user = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_USER.' u2 ON t.lastpost_user = u2.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON t.post_user = p.id
			 LEFT JOIN '.BS_TB_PROFILES.' p2 ON t.lastpost_user = p2.id
			 LEFT JOIN '.BS_TB_FORUMS.' rt ON ( t.rubrikid = rt.id )
			 '.$where.'
			 ORDER BY '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns the topic-data for the given id
	 *
	 * @param int $tid the topic-id
	 * @return array the topic-data or false if not found
	 */
	public function get_by_id($tid)
	{
		$rows = $this->get_by_ids(array($tid));
		if(count($rows) == 0)
			return false;
		
		return $rows[0];
	}
	
	/**
	 * Returns all topics with given ids. Optional you can force the forum-id
	 *
	 * @param array $tids all topic-ids
	 * @param int $fid the forum-id (0 = indifferent)
	 * @return array the topics
	 */
	public function get_by_ids($tids,$fid = 0)
	{
		if(!PLIB_Array_Utils::is_integer($tids) || count($tids) == 0)
			PLIB_Helper::def_error('intarray>0','tids',$tids);
		if(!PLIB_Helper::is_integer($fid) || $fid < 0)
			PLIB_Helper::def_error('intge0','fid',$fid);
		
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_THREADS.'
			 WHERE id IN ('.implode(',',$tids).')
			 '.($fid > 0 ? ' AND rubrikid = '.$fid : '')
		);
	}
	
	/**
	 * Returns all topics in the given forums. In addition to the topic-data you'll get wether
	 * the forum increases the experience or not
	 *
	 * @param array $fids the forums
	 * @return array the topics
	 */
	public function get_by_forums($fids)
	{
		if(!PLIB_Array_Utils::is_integer($fids) || count($fids) == 0)
			PLIB_Helper::def_error('intarray>0','fids',$fids);
		
		return $this->db->sql_rows(
			'SELECT t.*,increase_experience
			 FROM '.BS_TB_THREADS.' t
			 LEFT JOIN '.BS_TB_FORUMS.' f ON t.rubrikid = f.id
			 WHERE rubrikid IN ('.implode(',',$fids).')'
		);
	}
	
	/**
	 * Returns all topics that have been moved and link to the given topic-ids
	 *
	 * @param array $tids the topic-ids
	 * @return array the topics
	 */
	public function get_by_moved_ids($tids)
	{
		if(!PLIB_Array_Utils::is_integer($tids) || count($tids) == 0)
			PLIB_Helper::def_error('intarray>0','tids',$tids);
		
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_THREADS.'
			 WHERE moved_tid IN ('.implode(',',$tids).')'
		);
	}
	
	/**
	 * Returns the latest topics
	 *
	 * @param int $number the max. number of rows (0 = unlimited)
	 * @param array $excl_fids an array with forum-ids to exclude
	 * @return array the latest topics
	 */
	public function get_latest_topics($number = 0,$excl_fids = array())
	{
		if(!PLIB_Helper::is_integer($number) || $number < 0)
			PLIB_Helper::def_error('intge0','number',$number);
		if(!PLIB_Array_Utils::is_integer($excl_fids))
			PLIB_Helper::def_error('intarray','excl_fids',$excl_fids);
		
		return $this->db->sql_rows(
			'SELECT t.*,u.`'.BS_EXPORT_USER_NAME.'` username,u2.`'.BS_EXPORT_USER_NAME.'` lp_username,
							f.forum_name rubrikname,p.user_group
			 FROM '.BS_TB_THREADS.' t
			 LEFT JOIN '.BS_TB_USER.' u ON t.post_user = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_USER.' u2 ON t.lastpost_user = u2.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON t.lastpost_user = p.id
			 LEFT JOIN '.BS_TB_FORUMS.' f ON t.rubrikid = f.id
			 WHERE t.moved_tid = 0
			 '.(count($excl_fids) > 0 ? ' AND rubrikid NOT IN ('.implode(',',$excl_fids).')' : '').'
			 ORDER BY t.lastpost_id DESC
			 '.($number > 0 ? 'LIMIT '.$number : '')
		);
	}
	
	/**
	 * Returns all topics that have been started after the given time of the given user
	 *
	 * @param int $user_id the user-id
	 * @param int $start the start-time
	 * @return array all found topics
	 */
	public function get_topics_by_date($user_id,$start)
	{
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		if(!PLIB_Helper::is_integer($start) || $start < 0)
			PLIB_Helper::def_error('intge0','start',$start);
		
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_THREADS.'
			 WHERE post_time >= '.$start.' AND post_user = '.$user_id
		);
	}
	
	/**
	 * Returns all topics (grouped by the user-id) of the given users.
	 * You'll get the following fields:
	 * <code>
	 * 	array(
	 * 		'post_user' => <userId>,
	 * 		'topics' => <numberOfTopics>
	 * 	)
	 * </code>
	 *
	 * @param array $user_ids the user-ids
	 * @return array the topics
	 */
	public function get_topics_of_users($user_ids)
	{
		if(!PLIB_Array_Utils::is_integer($user_ids) || count($user_ids) == 0)
			PLIB_Helper::def_error('intarray>0','user_ids',$user_ids);
		
		return $this->db->sql_rows(
			'SELECT post_user,COUNT(t.id) topics FROM '.BS_TB_THREADS.' t
			 LEFT JOIN '.BS_TB_FORUMS.' f ON t.rubrikid = f.id
			 WHERE post_user IN ('.implode(',',$user_ids).') AND f.increase_experience = 1
			 GROUP BY post_user'
		);
	}
	
	/**
	 * Returns the created topics for the statistics grouped by date.
	 * You get the fields:
	 * <code>
	 * 	array(
	 * 		'post_time',
	 * 		'num' // the number of topics
	 * 		'date', // the date as YYYYMM
	 *	)
	 * </code>
	 *
	 * @return array the found rows
	 */
	public function get_topic_stats_grouped_by_date()
	{
		return $this->db->sql_rows(
			'SELECT post_time,COUNT(id) num,
							CONCAT(YEAR(FROM_UNIXTIME(post_time)),MONTH(FROM_UNIXTIME(post_time))) date
			 FROM '.BS_TB_THREADS.'
			 GROUP BY date
			 ORDER BY post_time DESC'
		);
	}
	
	/**
	 * Returns the topic-data that should be stored in the topic-cache. It contains the 
	 * topic-data, the forum-name and the number of attachments
	 * for this topic.
	 *
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @return array the topic-data or false if not found
	 */
	public function get_topic_for_cache($fid,$tid)
	{
		if(!PLIB_Helper::is_integer($fid) || $fid <= 0)
			PLIB_Helper::def_error('intgt0','fid',$fid);
		if(!PLIB_Helper::is_integer($tid) || $tid <= 0)
			PLIB_Helper::def_error('intgt0','tid',$tid);
		
		$row = $this->db->sql_fetch(
			'SELECT t.*,r.forum_name,r.forum_type,COUNT(a.id) as attachment_num
			 FROM '.BS_TB_THREADS." AS t
			 LEFT JOIN ".BS_TB_FORUMS." AS r ON ( t.rubrikid = r.id )
			 LEFT JOIN ".BS_TB_ATTACHMENTS." AS a ON ( t.id = a.thread_id )
			 WHERE t.id = '".$tid."' AND t.rubrikid = '".$fid."'
			 GROUP BY t.id,a.id"
		);
		if(!$row)
			return false;
		
		return $row;
	}
	
	/**
	 * @return int the last post-time
	 */
	public function get_last_post_time()
	{
		$data = $this->db->sql_fetch(
			'SELECT lastpost_time FROM '.BS_TB_THREADS.'
			 ORDER BY lastpost_id DESC'
		);
		if(!$data)
			return 0;
		
		return $data['lastpost_time'];
	}
	
	/**
	 * @return int the next id that would be used by auto-increment
	 */
	public function get_next_id()
	{
		$info = $this->db->sql_fetch_assoc(
			$this->db->sql_qry('SHOW TABLE STATUS LIKE "'.BS_TB_THREADS.'"')
		);
		return $info['Auto_increment'];
	}
	
	/**
	 * Creates a new topic with the given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$this->db->sql_insert(BS_TB_THREADS,$fields);
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Assigns the given guest-name to all topics by the given user and all lastpost-occurrences.
	 *
	 * @param int $user_id the user-id
	 * @param string $user_name the name of the guest
	 */
	public function assign_topics_to_guest($user_id,$user_name)
	{
		if(!PLIB_Helper::is_integer($user_id) || $user_id <= 0)
			PLIB_Helper::def_error('intgt0','user_id',$user_id);
		
		$this->db->sql_update(BS_TB_THREADS,'WHERE post_user = '.$user_id,array(
			'post_user' => 0,
			'post_an_user' => $user_name
		));
		$this->db->sql_update(BS_TB_THREADS,'WHERE lastpost_user = '.$user_id,array(
			'lastpost_user' => 0,
			'lastpost_an_user' => $user_name
		));
	}
	
	/**
	 * Updates the given fields of the given topic
	 *
	 * @param int $tid the topic-id
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update($tid,$fields)
	{
		return $this->update_by_ids(array($tid),$fields);
	}
	
	/**
	 * Updates the given fields for all topics with given ids
	 *
	 * @param array $tids the topic-ids
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_ids($tids,$fields)
	{
		if(!PLIB_Array_Utils::is_integer($tids) || count($tids) == 0)
			PLIB_Helper::def_error('intarray>0','tids',$tids);
		
		$this->db->sql_update(BS_TB_THREADS,'WHERE id IN ('.implode(',',$tids).')',$fields);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Updates the given fields for all shadow-topics with given ids
	 *
	 * @param array $tids the topic-ids
	 * @param array $fields the fields to set
	 * @return int the number of affected rows
	 */
	public function update_shadows_by_ids($tids,$fields)
	{
		if(!PLIB_Array_Utils::is_integer($tids) || count($tids) == 0)
			PLIB_Helper::def_error('intarray>0','tids',$tids);
		
		$this->db->sql_update(BS_TB_THREADS,'WHERE moved_tid IN ('.implode(',',$tids).')',$fields);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Updates the properties (lastpost_id,lastpost_time,lastpost_user,lastpost_an_user,posts) of
	 * the given topic.
	 *
	 * @param int $tid the topic-id
	 * @param array $lastpost the data of the last-post with the fields 'id','post_time','post_user',
	 * 	and 'post_an_user'
	 * @param int $replies the number of replies to set
	 * @return int the number of affected rows
	 */
	public function update_properties($tid,$lastpost,$replies)
	{
		if(!PLIB_Helper::is_integer($tid) || $tid <= 0)
			PLIB_Helper::def_error('intgt0','tid',$tid);
		if(!PLIB_Helper::is_integer($replies) || $replies < 0)
			PLIB_Helper::def_error('intge0','replies',$replies);
		
		$fields = array(
			'posts' => $replies,
		);
		$fields['lastpost_id'] = $lastpost['id'];
		$fields['lastpost_time'] = $lastpost['post_time'];
		$fields['lastpost_user'] = $lastpost['post_user'];
		if($lastpost['post_user'] != 0)
			$fields['lastpost_an_user'] = NULL;
		else
			$fields['lastpost_an_user'] = $lastpost['post_an_user'];
		
		$this->db->sql_update(BS_TB_THREADS,'WHERE id = '.$tid,$fields);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all topics with given ids
	 *
	 * @param array $ids the topic-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		return $this->_delete_by('id',$ids);
	}
	
	/**
	 * Deletes all topics in the given forums
	 *
	 * @param array $fids the forum-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_forums($fids)
	{
		return $this->_delete_by('rubrikid',$fids);
	}
	
	/**
	 * Deletes all shadow-topics that link to the given topic-ids
	 *
	 * @param array $tids the topic-ids
	 * @return int the number of affected rows
	 */
	public function delete_shadow_topics($tids)
	{
		return $this->_delete_by('moved_tid',$tids);
	}
	
	/**
	 * Deletes rows where the given field is one of the given ids
	 *
	 * @param string $field the field-name
	 * @param array $ids the ids
	 * @return unknown
	 */
	protected function _delete_by($field,$ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_THREADS.'
			 WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
}
?>