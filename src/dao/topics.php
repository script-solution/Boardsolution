<?php
/**
 * Contains the topics-dao-class
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
class BS_DAO_Topics extends FWS_Singleton
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
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_THREADS,'id','');
	}
	
	/**
	 * @param int $fid the forum-id
	 * @return int the number of topics in the given forum
	 */
	public function get_count_in_forum($fid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		
		return $db->get_row_count(BS_TB_THREADS,'id',' WHERE rubrikid = '.$fid);
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
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_THREADS.' t','t.id',$where);
	}
	
	/**
	 * Returns all topics in the given range.
	 *
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all found topics
	 */
	public function get_list($start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_THREADS.'
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
	 * @param array $keywords an array of keywords which will be used for a "fulltext-search".
	 * 	You may use "relevance" for sorting if the keywords are specified (not null)
	 * @return array all found topics
	 */
	public function get_list_by_search($where,$order = 't.id ASC',$start = 0,$count = 0,$keywords = null)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		$kw_add = '';
		if($keywords !== null)
		{
			$kw_add = ',(0';
			$sub = '0';
			foreach($keywords as $kw)
			{
				$kw_add .= ' + (LENGTH(t.name) - LENGTH(REPLACE(LOWER(t.name),LOWER("'.$kw.'"),"")))';
				$kw_add .= ' / LENGTH("'.$kw.'")';
				$sub .= '+ ((LENGTH(text_posted) - LENGTH(REPLACE(LOWER(text_posted),';
				$sub .= 'LOWER("'.$kw.'"),""))) / LENGTH("'.$kw.'"))';
			}
			if($db->get_server_version() >= '4.1')
			{
				$kw_add .= ' + (SELECT SUM('.$sub.') FROM '.BS_TB_POSTS;
				$kw_add .= ' WHERE threadid = t.id GROUP BY threadid LIMIT 1)';
			}
			$kw_add .= ') AS relevance';
		}
		
		return $db->get_rows(
			'SELECT t.*,u.`'.BS_EXPORT_USER_NAME.'` username,
							u2.`'.BS_EXPORT_USER_NAME.'` lp_username,rt.forum_name rubrikname,
							p.user_group,p2.user_group lastpost_user_group'.$kw_add.'
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
	 * @return array|bool the topic-data or false if not found
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($tids) || count($tids) == 0)
			FWS_Helper::def_error('intarray>0','tids',$tids);
		if(!FWS_Helper::is_integer($fid) || $fid < 0)
			FWS_Helper::def_error('intge0','fid',$fid);
		
		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($fids) || count($fids) == 0)
			FWS_Helper::def_error('intarray>0','fids',$fids);
		
		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($tids) || count($tids) == 0)
			FWS_Helper::def_error('intarray>0','tids',$tids);
		
		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($number) || $number < 0)
			FWS_Helper::def_error('intge0','number',$number);
		if(!FWS_Array_Utils::is_integer($excl_fids))
			FWS_Helper::def_error('intarray','excl_fids',$excl_fids);
		
		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		
		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($user_ids) || count($user_ids) == 0)
			FWS_Helper::def_error('intarray>0','user_ids',$user_ids);
		
		return $db->get_rows(
			'SELECT post_user,COUNT(t.id) topics FROM '.BS_TB_THREADS.' t
			 LEFT JOIN '.BS_TB_FORUMS.' f ON t.rubrikid = f.id
			 WHERE post_user IN ('.implode(',',$user_ids).') AND f.increase_experience = 1
			 GROUP BY post_user'
		);
	}
	
	/**
	 * Returns a list of users sorted descending by the number of created topics. You'll get the
	 * fields:
	 * <code>array(
	 * 	'num' => <numberOfTopics>,
	 * 	'user_name' => <userName>,
	 * 	'user_id' => <userId>,
	 * 	'user_group' => <userGroupList>
	 * )</code>
	 *
	 * @param int $number the max. number of users
	 * @return array all found entries
	 */
	public function get_topic_creation_stats($number)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($number) || $number <= 0)
			FWS_Helper::def_error('intgt0','number',$number);
		
		return $db->get_rows(
			'SELECT COUNT(*) num,t.post_user user_id,u.`'.BS_EXPORT_USER_NAME.'` user_name,p.user_group
			 FROM '.BS_TB_THREADS.' t
			 LEFT JOIN '.BS_TB_USER.' u ON t.post_user = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON t.post_user = p.id
			 WHERE post_user > 0
			 GROUP BY post_user
			 ORDER BY num DESC
			 LIMIT '.$number
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
		$db = FWS_Props::get()->db();

		return $db->get_rows(
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
	 * @return array|bool the topic-data or false if not found
	 */
	public function get_topic_for_cache($fid,$tid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		$row = $db->get_row(
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
		$db = FWS_Props::get()->db();

		$data = $db->get_row(
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
		$db = FWS_Props::get()->db();

		$info = $db->get_row('SHOW TABLE STATUS LIKE "'.BS_TB_THREADS.'"');
		return $info['Auto_increment'];
	}

	/**
	 * Returns the original data of a shadow-topic
	 *
	 * @param int $id the original-topic-id
	 * @return array with the field name, symbol, comallow and important
	 */
	public function get_original_data_of_shadow_topic($id)
	{
		$db = FWS_Props::get()->db();
	
		return $db->get_row(
				'SELECT name, symbol, comallow, important
			 FROM '.BS_TB_THREADS.'
			 WHERE id = '.$id);
	}
	
	/**
	 * Creates a new topic with the given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_THREADS,$fields);
	}
	
	/**
	 * Assigns the given guest-name to all topics by the given user and all lastpost-occurrences.
	 *
	 * @param int $user_id the user-id
	 * @param string $user_name the name of the guest
	 */
	public function assign_topics_to_guest($user_id,$user_name)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$db->update(BS_TB_THREADS,'WHERE post_user = '.$user_id,array(
			'post_user' => 0,
			'post_an_user' => $user_name
		));
		$db->update(BS_TB_THREADS,'WHERE lastpost_user = '.$user_id,array(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($tids) || count($tids) == 0)
			FWS_Helper::def_error('intarray>0','tids',$tids);
		
		return $db->update(BS_TB_THREADS,'WHERE id IN ('.implode(',',$tids).')',$fields);
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
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($tids) || count($tids) == 0)
			FWS_Helper::def_error('intarray>0','tids',$tids);
		
		return $db->update(BS_TB_THREADS,'WHERE moved_tid IN ('.implode(',',$tids).')',$fields);
	}
	
	/**
	 * Updates the properties (lastpost_id,lastpost_time,lastpost_user,lastpost_an_user,posts) of
	 * the given topic.
	 *
	 * @param int $tid the topic-id
	 * @param array $needed the name, symbol and settings of the topic
	 * @param array $lastpost the data of the last-post with the fields 'id','post_time','post_user',
	 * 	and 'post_an_user'
	 * @param int $replies the number of replies to set
	 * @return int the number of affected rows
	 */
	public function update_properties($tid,$lastpost,$replies,$main)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		if(!FWS_Helper::is_integer($replies) || $replies < 0)
			FWS_Helper::def_error('intge0','replies',$replies);
		
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

		if($main)
		{
			$fields['name'] = $main['name'];
			$fields['symbol'] = $main['symbol'];
			$fields['comallow'] = $main['comallow'];
			$fields['important'] = $main['important'];
		}
		
		return $db->update(BS_TB_THREADS,'WHERE id = '.$tid,$fields);
	}
	
	/**
	 * Deletes all topics with given ids
	 *
	 * @param array $ids the topic-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		return $this->delete_by('id',$ids);
	}
	
	/**
	 * Deletes all topics in the given forums
	 *
	 * @param array $fids the forum-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_forums($fids)
	{
		return $this->delete_by('rubrikid',$fids);
	}
	
	/**
	 * Deletes all shadow-topics that link to the given topic-ids
	 *
	 * @param array $tids the topic-ids
	 * @return int the number of affected rows
	 */
	public function delete_shadow_topics($tids)
	{
		return $this->delete_by('moved_tid',$tids);
	}
	
	/**
	 * Deletes rows where the given field is one of the given ids
	 *
	 * @param string $field the field-name
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	protected function delete_by($field,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_THREADS.'
			 WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>