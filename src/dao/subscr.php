<?php
/**
 * Contains the subscriptions-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the subscriptions-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Subscr extends FWS_Singleton
{
	/**
	 * @return BS_DAO_Subscr the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the total number of subscriptions
	 */
	public function get_count()
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_SUBSCR,'id','');
	}
	
	/**
	 * @param string $keyword the keyword you want to search for
	 * @return int the total number of subscriptions
	 */
	public function get_count_by_keyword($keyword)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(
			BS_TB_SUBSCR.' s','s.id','LEFT JOIN '.BS_TB_FORUMS.' f ON f.id = s.forum_id
			 LEFT JOIN '.BS_TB_THREADS.' t ON t.id = s.topic_id
			 LEFT JOIN '.BS_TB_USER.' u ON u.`'.BS_EXPORT_USER_ID.'` = s.user_id
			 WHERE f.forum_name LIKE "%'.$keyword.'%" OR t.name LIKE "%'.$keyword.'%"
			 	OR u.`'.BS_EXPORT_USER_NAME.'` LIKE "%'.$keyword.'%"'
		);
	}
	
	/**
	 * The number of topic-subscriptions of the given user
	 *
	 * @param int $user_id the user-id
	 * @return int the number of topic-subscriptions
	 */
	public function get_subscr_topics_count($user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->get_row_count(BS_TB_SUBSCR,'id',' WHERE topic_id != 0 AND user_id = '.$user_id);
	}
	
	/**
	 * The number of forum-subscriptions of the given user
	 *
	 * @param int $user_id the user-id
	 * @return int the number of forum-subscriptions
	 */
	public function get_subscr_forums_count($user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->get_row_count(BS_TB_SUBSCR,'id',' WHERE forum_id != 0 AND user_id = '.$user_id);
	}
	
	/**
	 * Checks wether the given user has already subscribed the given topic
	 *
	 * @param int $user_id the user-id
	 * @param int $tid the topic-id
	 * @return boolean true if so
	 */
	public function has_subscribed_topic($user_id,$tid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		return $db->get_row_count(
			BS_TB_SUBSCR,'id',' WHERE topic_id = '.$tid.' AND user_id = '.$user_id
		) > 0;
	}
	
	/**
	 * Checks wether the given user has already subscribed the given forum
	 *
	 * @param int $user_id the user-id
	 * @param int $fid the forum-id
	 * @return boolean true if so
	 */
	public function has_subscribed_forum($user_id,$fid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		
		return $db->get_row_count(
			BS_TB_SUBSCR,'id',' WHERE forum_id = '.$fid.' AND user_id = '.$user_id
		) > 0;
	}
	
	/**
	 * Returns a list with all subscriptions and additional infos from other tables:
	 * <code>
	 * 	array(
	 * 		'forum_name' => ...,
	 * 		'name' => <topicName>,
	 * 		'lastpost_time' => <lastPostTimeOfTopic>,
	 * 		'user_name' => <subscriberUserName>,
	 * 		'lastlogin' => <subscriberLastLogin>,
	 * 		'flastpost_time' => <lastPostTimeOfForum>
	 * 	)
	 * </code>
	 *
	 * @param string $keyword the keyword you want to search for (empty = all)
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array the subscriptions
	 */
	public function get_list($keyword,$sort = 'id',$order = 'ASC',$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT
				s.*,f.forum_name,t.name,t.lastpost_time,u.`'.BS_EXPORT_USER_NAME.'` user_name,
				p.lastlogin,po.post_time flastpost_time
			 FROM '.BS_TB_SUBSCR.' s
			 LEFT JOIN '.BS_TB_FORUMS.' f ON f.id = s.forum_id
			 LEFT JOIN '.BS_TB_THREADS.' t ON t.id = s.topic_id
			 LEFT JOIN '.BS_TB_USER.' u ON u.`'.BS_EXPORT_USER_ID.'` = s.user_id
			 LEFT JOIN '.BS_TB_PROFILES.' p ON p.id = s.user_id
			 LEFT JOIN '.BS_TB_POSTS.' po ON f.lastpost_id = po.id
			 '.($keyword ? 'WHERE f.forum_name LIKE "%'.$keyword.'%" OR t.name LIKE "%'.$keyword.'%"
			 	OR user_name LIKE "%'.$keyword.'%"' : '').'
			 ORDER BY '.$sort.' '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns all subscribed topics of the given user. Optional you can specify the topic-ids.
	 * You can specify the range. You'll get all fields of the topic and subscription.
	 *
	 * @param int $user_id the user-id
	 * @param array $ids the topic-ids (empty array = ignore)
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array the subscriptions
	 */
	public function get_subscr_topics_of_user($user_id,$ids = array(),$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Array_Utils::is_integer($ids))
			FWS_Helper::def_error('intarray','ids',$ids);
		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT s.*,t.*,s.id
			 FROM '.BS_TB_SUBSCR.' s
			 LEFT JOIN '.BS_TB_THREADS.' t ON s.topic_id = t.id
			 WHERE user_id = '.$user_id.' AND topic_id > 0
			 '.(count($ids) > 0 ? ' AND topic_id IN ('.implode(',',$ids).')' : '').'
			 ORDER BY s.sub_date DESC
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns all subscribed forums of the given user. Optional you can specify the forum-ids
	 *
	 * @param int $user_id the user-id
	 * @param array $ids the forum-ids (empty array = ignore)
	 * @return array the subscriptions
	 */
	public function get_subscr_forums_of_user($user_id,$ids = array())
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Array_Utils::is_integer($ids))
			FWS_Helper::def_error('intarray','ids',$ids);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_SUBSCR.'
			 WHERE user_id = '.$user_id.' AND forum_id != 0
			 '.(count($ids) > 0 ? ' AND forum_id IN ('.implode(',',$ids).')' : '')
		);
	}
	
	/**
	 * Returns all subscribed users for the given forum and/or topic. You'll get the following fields:
	 * <code>
	 * 	array(
	 * 		'user_email' => ...,
	 * 		'emails_include_post' => ...
	 * )
	 * </code>
	 * The specified user-id will exclude this user from the list.
	 *
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param int $user_id the user-id (may be 0)
	 * @return array all subscribed users
	 */
	public function get_subscribed_users($fid,$tid,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		if(!FWS_Helper::is_integer($user_id) || $user_id < 0)
			FWS_Helper::def_error('intge0','user_id',$user_id);
		
		return $db->get_rows(
			'SELECT u.`'.BS_EXPORT_USER_EMAIL.'` user_email,p.emails_include_post
			 FROM '.BS_TB_SUBSCR.' s
			 LEFT JOIN '.BS_TB_PROFILES.' p ON s.user_id = p.id
			 LEFT JOIN '.BS_TB_USER.' u ON s.user_id = u.`'.BS_EXPORT_USER_ID.'`
			 WHERE (s.topic_id = '.$tid.' OR s.forum_id = '.$fid.')	AND
						 s.user_id != '.$user_id.' AND email_notification_type = "immediatly"
			 GROUP BY p.id'
		);
	}
	
	/**
	 * Returns a list with all ids of the subscriptions that "timed out"
	 *
	 * @param int $timeout the timeout (in seconds)
	 * @return array all ids
	 */
	public function get_timedout_subscr_ids($timeout)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($timeout) || $timeout <= 0)
			FWS_Helper::def_error('intgt0','timeout',$timeout);
		
		$rows = $db->get_rows(
			'SELECT s.id
			 FROM '.BS_TB_SUBSCR.' s
			 LEFT JOIN '.BS_TB_THREADS.' t ON t.id = s.topic_id
			 WHERE (t.lastpost_time IS NULL OR t.lastpost_time < '.(time() - $timeout).')
			 	AND s.topic_id != 0'
		);
		$ids = array();
		foreach($rows as $row)
			$ids[] = $row['id'];
		return $ids;
	}
	
	/**
	 * Subscribes the given forum with the given user
	 *
	 * @param int $fid the forum-id
	 * @param int $user_id the user-id
	 * @return int the used id
	 */
	public function subscribe_forum($fid,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->insert(BS_TB_SUBSCR,array(
			'forum_id' => $fid,
			'user_id' => $user_id,
			'sub_date' => time()
		));
	}
	
	/**
	 * Subscribes the given topic with the given user
	 *
	 * @param int $tid the topic-id
	 * @param int $user_id the user-id
	 * @return int the used id
	 */
	public function subscribe_topic($tid,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->insert(BS_TB_SUBSCR,array(
			'topic_id' => $tid,
			'user_id' => $user_id,
			'sub_date' => time()
		));
	}
	
	/**
	 * Deletes the subscriptions with given ids of the given user
	 *
	 * @param array $ids all ids to delete
	 * @param int $user_id the user-id
	 * @return int the number of affected rows
	 */
	public function delete_by_ids_from_user($ids,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$db->execute(
			'DELETE FROM '.BS_TB_SUBSCR.'
			 WHERE id IN ('.implode(',',$ids).') AND user_id = '.$user_id
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes the subscriptions with given ids
	 *
	 * @param array $ids all ids to delete
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		return $this->delete_by('id',$ids);
	}
	
	/**
	 * Deletes the subscriptions of given forums
	 *
	 * @param array $fids all forum-ids to delete
	 * @return int the number of affected rows
	 */
	public function delete_by_forums($fids)
	{
		return $this->delete_by('forum_id',$fids);
	}
	
	/**
	 * Deletes the subscriptions of given topics
	 *
	 * @param array $tids all topic-ids to delete
	 * @return int the number of affected rows
	 */
	public function delete_by_topics($tids)
	{
		return $this->delete_by('topic_id',$tids);
	}
	
	/**
	 * Deletes the entries with given ids
	 *
	 * @param string $field the field-name
	 * @param array $ids all ids to delete
	 * @return int the number of affected rows
	 */
	protected function delete_by($field,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_SUBSCR.' WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>