<?php
/**
 * Contains the posts-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the posts-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Posts extends FWS_Singleton
{
	/**
	 * @return BS_DAO_Posts the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the total number of posts
	 */
	public function get_count()
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_POSTS,'id','');
	}
	
	/**
	 * @param int $id the forum-id
	 * @return int the total number of posts in the given forum
	 */
	public function get_count_in_forum($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->get_row_count(BS_TB_POSTS,'id',' WHERE rubrikid = '.$id);
	}
	
	/**
	 * @param int $id the topic-id
	 * @return int the total number of posts in the given topic
	 */
	public function get_count_in_topic($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->get_row_count(BS_TB_POSTS,'id',' WHERE threadid = '.$id);
	}
	
	/**
	 * @return int the number of posts today
	 */
	public function get_count_today()
	{
		$db = FWS_Props::get()->db();

		$now = new FWS_Date();
		$y = $now->get_year();
		$d = $now->get_day();
		$m = $now->get_month();
		$today_start = FWS_Date::get_timestamp(array(0,0,0,$m,$d,$y));
		return $db->get_row_count(BS_TB_POSTS,'id',' WHERE post_time >= '.$today_start);
	}
	
	/**
	 * @return int the number of posts yesterday
	 */
	public function get_count_yesterday()
	{
		$db = FWS_Props::get()->db();

		$now = new FWS_Date();
		$y = $now->get_year();
		$d = $now->get_day();
		$m = $now->get_month();
		$yesterday_start = FWS_Date::get_timestamp(array(0,0,0,$m,$d - 1,$y));
		$yesterday_end = FWS_Date::get_timestamp(array(23,59,59,$m,$d - 1,$y));
		return $db->get_row_count(
			BS_TB_POSTS,'id',' WHERE post_time >= '.$yesterday_start.' AND post_time <= '.$yesterday_end
		);
	}
	
	/**
	 * Determines the id of the first post in the given topic
	 *
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @return int the first-post-id
	 */
	public function get_first_postid_in_topic($fid,$tid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		$data = $db->get_row(
			'SELECT MIN(id) AS min FROM '.BS_TB_POSTS.'
			 WHERE threadid = '.$tid.' AND rubrikid = '.$fid
		);
		if(!$data)
			return 0;
		
		return $data['min'];
	}
	
	/**
	 * Determines the time of the last post
	 *
	 * @return int the last post time
	 */
	public function get_lastpost_time()
	{
		$db = FWS_Props::get()->db();

		$data = $db->get_row(
			'SELECT MAX(post_time) AS t FROM '.BS_TB_POSTS
		);
		if(!$data)
			return 0;
		
		return $data['t'];
	}
	
	/**
	 * Determines the time of the last edit
	 *
	 * @return int the last edit time
	 */
	public function get_lastedit_time()
	{
		$db = FWS_Props::get()->db();

		$data = $db->get_row(
			'SELECT MAX(edited_date) AS t FROM '.BS_TB_POSTS
		);
		if(!$data)
			return 0;
		
		return $data['t'];
	}
	
	/**
	 * Returns a list of posts at any location. You can sort the result and specify a start-position
	 * and max. number.
	 *
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement)
	 * @return array all found posts
	 */
	public function get_list($sort = 'id',$order = 'ASC',$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_POSTS.'
			 ORDER BY '.$sort.' '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns a list of posts from the given topic. You may also sort the result.
	 *
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @return array all found posts
	 */
	public function get_all_from_topic($fid,$tid,$sort = 'id',$order = 'ASC')
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_POSTS.'
			 WHERE threadid = '.$tid.' AND rubrikid = '.$fid.'
			 ORDER BY '.$sort.' '.$order
		);
	}
	
	/**
	 * Returns the data of the post with given id. You'll get all fields from the post-table
	 * and the user-name for post_user.
	 *
	 * @param int $id the post-id
	 * @return array the post-data or false if not found
	 */
	public function get_post_by_id($id)
	{
		$rows = $this->get_posts_by_ids(array($id));
		if(count($rows) == 0)
			return false;
		
		return $rows[0];
	}
	
	/**
	 * Returns a list with all posts with given ids. Optional you can specify the topic- and forum-id.
	 * You'll get all fields from the post-table and the user-name for post_user.
	 *
	 * @param array $ids all post-ids to get
	 * @param int $fid the forum-id (0 = indifferent)
	 * @param int $tid the topic-id (0 = indifferent)
	 * @return array all found posts
	 */
	public function get_posts_by_ids($ids,$fid = 0,$tid = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		if(!FWS_Helper::is_integer($fid) || $fid < 0)
			FWS_Helper::def_error('intge0','fid',$fid);
		if(!FWS_Helper::is_integer($tid) || $tid < 0)
			FWS_Helper::def_error('intge0','tid',$tid);
		
		$where = 'WHERE p.id IN ('.implode(',',$ids).')';
		if($fid > 0)
			$where .= ' AND rubrikid = '.$fid;
		if($tid > 0)
			$where .= ' AND threadid = '.$tid;
		
		return $db->get_rows(
			'SELECT p.*,u.`'.BS_EXPORT_USER_NAME.'` user_name FROM '.BS_TB_POSTS.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.post_user = u.`'.BS_EXPORT_USER_ID.'`
			 '.$where
		);
	}
	
	/**
	 * Returns all posts from the given topics. You'll get all fields of the posts-table.
	 *
	 * @param array $tids all topic-ids
	 * @return array all found posts
	 */
	public function get_posts_by_topics($tids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($tids) || count($tids) == 0)
			FWS_Helper::def_error('intarray>0','tids',$tids);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_POSTS.'
			 WHERE threadid IN ('.implode(',',$tids).')'
		);
	}
	
	/**
	 * Searches for posts with the given WHERE-clause. You may also sort the result.
	 * <var>$type</var> specifies wether the result should be grouped by the topic-id or
	 * not.
	 *
	 * @param string $where the WHERE-clause
	 * @param string $order the value for the ORDER BY clause
	 * @param string $type the type: topics or posts
	 * @param int $number the max. number of results (0 = unlimited)
	 * @param array $keywords an array of keywords which will be used for a "fulltext-search".
	 * 	You may use "relevance" for sorting if the keywords are specified (not null)
	 * @return array all found posts
	 */
	public function get_posts_by_search($where,$order = 'p.id ASC',$type = 'topics',$number = 0,
		$keywords = null)
	{
		$db = FWS_Props::get()->db();

		if(!in_array($type,array('topics','posts')))
			FWS_Helper::def_error('inarray','type',array('topics','posts'),$type);
		if(!FWS_Helper::is_integer($number) || $number < 0)
			FWS_Helper::def_error('intge0','number',$number);
		if($keywords !== null && !is_array($keywords))
			FWS_Helper::def_error('array','keywords',$keywords);
		
		$kw_add = '';
		if($keywords !== null)
		{
			$kw_add = ',(0';
			foreach($keywords as $kw)
			{
				$kw_add .= ' + (LENGTH(p.text_posted) - LENGTH(REPLACE(LOWER(p.text_posted)';
				$kw_add .= ',LOWER("'.$kw.'"),""))) / LENGTH("'.$kw.'")';
				$kw_add .= ' + (LENGTH(t.name) - LENGTH(REPLACE(LOWER(t.name)';
				$kw_add .= ',LOWER("'.$kw.'"),""))) / LENGTH("'.$kw.'")';
			}
			$kw_add .= ') AS relevance';
		}
		
		return $db->get_rows(
			'SELECT
			   p.id,p.threadid'.$kw_add.'
			 FROM '.BS_TB_POSTS.' p
			 LEFT JOIN '.BS_TB_THREADS.' t ON p.threadid = t.id
			 LEFT JOIN '.BS_TB_USER.' u ON p.post_user = u.`'.BS_EXPORT_USER_ID.'`
			 '.$where.'
			 '.($type == 'topics' ? 'GROUP BY p.threadid' : '').'
			 ORDER BY '.$order.'
		   '.($number > 0 ? 'LIMIT '.$number : '')
		);
	}
	
	/**
	 * Grabs posts from the database for the topic-display. You can define the WHERE-clause by
	 * yourself and specify the order and range.
	 *
	 * @param string $where the WHERE-clause
	 * @param string $order the value for the ORDER BY clause
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement)
	 * @param array $keywords an array of keywords which will be used for a "fulltext-search".
	 * 	You may use "relevance" for sorting if the keywords are specified (not null)
	 * @return array the found posts
	 */
	public function get_posts_for_topic($where,$order = 'p.id ASC',$start = 0,$count = 0,$keywords = null)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		if($keywords !== null && !is_array($keywords))
			FWS_Helper::def_error('array','keywords',$keywords);
		
		$kw_add = '';
		if($keywords !== null)
		{
			$kw_add = ',(0';
			foreach($keywords as $kw)
			{
				$kw_add .= ' + (LENGTH(p.text_posted) - LENGTH(REPLACE(LOWER(p.text_posted)';
				$kw_add .= ',LOWER("'.$kw.'"),""))) / LENGTH("'.$kw.'")';
				$kw_add .= ' + (LENGTH(t.name) - LENGTH(REPLACE(LOWER(t.name)';
				$kw_add .= ',LOWER("'.$kw.'"),""))) / LENGTH("'.$kw.'")';
			}
			$kw_add .= ') AS relevance';
		}
		
		return $db->get_rows(
			'SELECT p.*,u.`'.BS_EXPORT_USER_NAME.'` AS user,u.`'.BS_EXPORT_USER_EMAIL.'` email,
						  pr.*,p.id AS bid,pr.signatur bsignatur,a.av_pfad,a.user AS aowner,
						  u2.`'.BS_EXPORT_USER_NAME.'` edited_user_name,p2.user_group edited_user_group,
						  t.name'.$kw_add.'
			 FROM '.BS_TB_POSTS.' AS p
			 LEFT JOIN '.BS_TB_USER.' AS u ON ( p.post_user = u.`'.BS_EXPORT_USER_ID.'` )
			 LEFT JOIN '.BS_TB_PROFILES.' AS pr ON ( u.`'.BS_EXPORT_USER_ID.'` = pr.id )
			 LEFT JOIN '.BS_TB_AVATARS.' AS a ON ( pr.avatar = a.id )
			 LEFT JOIN '.BS_TB_USER.' AS u2 ON ( p.edited_user = u2.`'.BS_EXPORT_USER_ID.'` )
			 LEFT JOIN '.BS_TB_PROFILES.' p2 ON p.edited_user = p2.id
			 LEFT JOIN '.BS_TB_THREADS.' AS t ON p.threadid = t.id
			 '.$where.'
			 ORDER BY '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns the post with given id from the given topic. You'll get all fields from the posts-table,
	 * the user-name, the user-group, the default font, wether the topic is closed and wether it is
	 * locked.
	 *
	 * @param int $id the post-id
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @return array the post-data or false if not found
	 */
	public function get_post_from_topic($id,$fid,$tid)
	{
		$rows = $this->get_posts_from_topic(array($id),$fid,$tid);
		if(count($rows) == 0)
			return false;
		
		return $rows[0];
	}
	
	/**
	 * Returns all posts with given ids from the given topic. You'll get all fields from the
	 * posts-table, the user-name, the user-group, the default font, wether the topic is closed
	 * and wether it is locked.
	 * Additionally you can sort the result
	 *
	 * @param array $ids the post-ids
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @param string $sort the column to sort by
	 * @param string $order the order: ASC or DESC
	 * @return array all found posts
	 */
	public function get_posts_from_topic($ids,$fid,$tid,$sort = 'p.id',$order = 'ASC')
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		return $db->get_rows(
			'SELECT
				p.*,u.`'.BS_EXPORT_USER_NAME.'` user_name,pr.user_group,pr.default_font,
				t.thread_closed,t.locked
			 FROM '.BS_TB_POSTS.' p
			 LEFT JOIN '.BS_TB_THREADS.' t ON t.id = p.threadid
			 LEFT JOIN '.BS_TB_USER.' u ON p.post_user = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' pr ON p.post_user = pr.id
			 WHERE p.id IN ('.implode(',',$ids).') AND p.rubrikid = '.$fid.' AND p.threadid = '.$tid.'
			 ORDER BY '.$sort.' '.$order
		);
	}
	
	/**
	 * Returns all posts which have been posted from the user with given id and after the given
	 * <var>$mindate</var>.
	 *
	 * @param int $user_id the user-id
	 * @param int $mindate the min. timestamp of the post-date
	 * @return array all found posts
	 */
	public function get_posts_by_date($user_id,$mindate)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($mindate) || $mindate < 0)
			FWS_Helper::def_error('intge0','mindate',$mindate);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_POSTS.'
			 WHERE post_time >= '.$mindate.' AND post_user = '.$user_id
		);
	}
	
	/**
	 * Returns all posts for the "new posts email". That means you get all posts with the given ids
	 * sorted by thread-id and post-time ascending.
	 *
	 * @param array $ids all post-ids
	 * @return array all found posts
	 */
	public function get_posts_for_email($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT p.*,t.name,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_POSTS.' p
			 LEFT JOIN '.BS_TB_THREADS.' t ON p.threadid = t.id
			 LEFT JOIN '.BS_TB_USER.' u ON u.`'.BS_EXPORT_USER_ID.'` = p.post_user
			 WHERE p.id IN ('.implode(',',$ids).')
			 ORDER BY p.threadid ASC,p.post_time ASC'
		);
	}
	
	/**
	 * Determines the first post of the given topic-ids. You'll get the following fields:
	 * <code>
	 * 	array(
	 * 		'id' => <topicID>,
	 * 		'rubrikid' => <forumID>,
	 * 		'moved_tid' => <movedTopicID>,
	 * 		'first_post' => <idOfFirstPost>,
	 * 		'posts' => <numberOfPostsInTopic>
	 * 	)
	 * </code>
	 *
	 * @param array $tids the topic-ids
	 * @return array an array with the first posts in the topics
	 */
	public function get_first_post_from_topics($tids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($tids) || count($tids) == 0)
			FWS_Helper::def_error('intarray>0','tids',$tids);
		
		return $db->get_rows(
			'SELECT t.id,p.rubrikid,p.threadid,moved_tid,MIN(p.id) first_post,posts
			 FROM '.BS_TB_POSTS.' p
			 LEFT JOIN '.BS_TB_THREADS.' t ON p.threadid = t.id
			 WHERE p.threadid IN ('.implode(',',$tids).') AND moved_tid = 0
			 GROUP BY t.id'
		);
	}
	
	/**
	 * Determines the id of the next post in the given topic. Or in other words: The post-id
	 * that is greater as the given one in the given topic, sorted by id ascending.
	 *
	 * @param int $id the post-id
	 * @param int $tid the topic-id
	 * @return int the post-id or false if not found
	 */
	public function get_next_post_id($id,$tid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		$row = $db->get_row(
			'SELECT id FROM '.BS_TB_POSTS.'
			 WHERE id > '.$id.' AND threadid = '.$tid.'
			 ORDER BY id ASC'
		);
		if(!$row)
			return false;
		
		return $row['id'];
	}
	
	/**
	 * Returns the posts that are greater or equal than the given post-id in the given topic.
	 *
	 * @param int $id the post-id
	 * @param int $fid the forum-id
	 * @param int $tid the topic-id
	 * @return array all following posts
	 */
	public function get_following_posts($id,$fid,$tid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_POSTS.'
			 WHERE id >= '.$id.' AND rubrikid = '.$fid.' AND threadid = '.$tid
		);
	}
	
	/**
	 * Grabs all news (the first post of a topic) from the given forums from the database.
	 * You'll get all fields from the posts-table, the user-name, the user-group, the topic-name,
	 * number of posts and wether comments are allowed.
	 * The result will be sorted by thread-id descending.
	 * Note that you have to exclude the denied forums from the forum-ids
	 *
	 * @param array $fids the forum-ids
	 * @param int $number the max. number of news
	 * @return array all news
	 */
	public function get_news_from_forums($fids,$number = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($fids) || count($fids) == 0)
			FWS_Helper::def_error('intarray>0','fids',$fids);
		if(!FWS_Helper::is_integer($number) || $number < 0)
			FWS_Helper::def_error('intge0','number',$number);
		
		$rows = $db->get_rows(
			'SELECT MIN(b.id) first_post_id
			 FROM '.BS_TB_POSTS.' b
			 WHERE b.rubrikid IN ('.implode(',',$fids).')
			 GROUP BY b.threadid
			 ORDER BY b.threadid DESC
			 '.($number > 0 ? 'LIMIT '.$number : '')
		);
		$post_ids = array();
		foreach($rows as $data)
			$post_ids[] = $data['first_post_id'];
		
		if(count($post_ids) > 0)
		{
			return $db->get_rows(
				'SELECT
					b.*,u.`'.BS_EXPORT_USER_NAME.'` user_name,p.user_group,t.name,t.posts,t.comallow
				 FROM '.BS_TB_POSTS.' b
				 LEFT JOIN '.BS_TB_USER.' u ON b.post_user = u.`'.BS_EXPORT_USER_ID.'`
				 LEFT JOIN '.BS_TB_PROFILES.' p ON u.`'.BS_EXPORT_USER_ID.'` = p.id
				 LEFT JOIN '.BS_TB_THREADS.' t ON b.threadid = t.id
				 WHERE b.id IN ('.implode(',',$post_ids).')
				 ORDER BY b.threadid DESC'
			);
		}
		
		return array();
	}
	
	/**
	 * Returns all unread posts since the given date for the given user. You can exclude some
	 * forums and specify a max. number of rows.
	 *
	 * @param int $since the since-date (as timestamp)
	 * @param int $user_id the user-id (0 = doesn't matter)
	 * @param array $excl_fids an array of forums to exclude
	 * @param array $excl_tids an array of topics to exclude
	 * @param int $limit a max. number of rows (0 = unlimited)
	 * @return array all unread posts since the given date
	 */
	public function get_unread_posts($since,$user_id,$excl_fids = array(),$excl_tids = array(),$limit = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($since) || $since < 0)
			FWS_Helper::def_error('intge0','since',$since);
		if(!FWS_Helper::is_integer($user_id) || $user_id < 0)
			FWS_Helper::def_error('intge0','user_id',$user_id);
		if(!FWS_Array_Utils::is_integer($excl_fids))
			FWS_Helper::def_error('intarray','excl_fids',$excl_fids);
		if(!FWS_Array_Utils::is_integer($excl_tids))
			FWS_Helper::def_error('intarray','excl_tids',$excl_tids);
		if(!FWS_Helper::is_integer($limit) || $limit < 0)
			FWS_Helper::def_error('intge0','limit',$limit);
		
		return $db->get_rows(
			'SELECT MIN(id) AS first_unread_post,threadid,rubrikid FROM '.BS_TB_POSTS.'
			 WHERE ((post_time > '.$since.($user_id > 0 ? ' AND post_user != '.$user_id : '').') OR
			 			 (edited_date > '.$since.($user_id > 0 ? ' AND edited_user != '.$user_id : '').'))
						 '.(count($excl_fids) > 0 ? 'AND rubrikid NOT IN ('.implode(',',$excl_fids).')' : '').'
						 '.(count($excl_tids) > 0 ? 'AND threadid NOT IN ('.implode(',',$excl_tids).')' : '').'
			 GROUP BY threadid
			 ORDER BY threadid DESC
			 '.($limit > 0 ? 'LIMIT '.$limit : '')
		);
	}
	
	/**
	 * Returns the number of posts of the given users. It will be grouped by user and
	 * increase-experience. You'll get the following fields:
	 * <code>
	 * 	array(
	 * 		'num' => <numberOfPosts>,
	 * 		'post_user' => <userId>,
	 * 		'increase_experience' => ... // wether the posts should increase the experience
	 * 	)
	 * </code>
	 *
	 * @param array $ids the user-ids
	 * @return array all posts of the user grouped by user and increase-experience
	 */
	public function get_user_posts_of_users($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT COUNT(p.id) num,post_user,increase_experience
			 FROM '.BS_TB_POSTS.' p
			 LEFT JOIN '.BS_TB_FORUMS.' f ON p.rubrikid = f.id
			 WHERE post_user IN ('.implode(',',$ids).')
			 GROUP BY post_user,increase_experience'
		);
	}
	
	/**
	 * Returns the number of posts in the given forums. It will be grouped by user and
	 * increase-experience. You'll get the following fields:
	 * <code>
	 * 	array(
	 * 		'num' => <numberOfPosts>,
	 * 		'post_user' => <userId>,
	 * 		'increase_experience' => ... // wether the posts should increase the experience
	 * 	)
	 * </code>
	 *
	 * @param array $fids the forum-ids
	 * @return array all posts of the user grouped by user and increase-experience
	 */
	public function get_user_posts_in_forums($fids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($fids) || count($fids) == 0)
			FWS_Helper::def_error('intarray>0','fids',$fids);
		
		return $db->get_rows(
			'SELECT COUNT(*) num,post_user,f.increase_experience
			 FROM '.BS_TB_POSTS.' p
			 LEFT JOIN '.BS_TB_FORUMS.' f ON p.rubrikid = f.id
			 WHERE post_user != 0 AND rubrikid IN ('.implode(',',$fids).')
			 GROUP BY post_user,f.increase_experience'
		);
	}
	
	/**
	 * Calculates the lastpost-id for the given forum. If you specify post-ids this ids will
	 * be excluded
	 *
	 * @param int $fid the id of the forum
	 * @param array $post_ids an numeric array with the post-ids to exclude
	 * @return int the lastpost-id
	 */
	public function get_lastpost_id_in_forum($fid,$post_ids = array())
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($fid) || $fid <= 0)
			FWS_Helper::def_error('intgt0','fid',$fid);
		if(!FWS_Array_Utils::is_integer($post_ids))
			FWS_Helper::def_error('intarray','post_ids',$post_ids);
		
		$exclude = count($post_ids) > 0 ? ' AND id NOT IN ('.implode(',',$post_ids).')' : '';
		$data = $db->get_row(
			'SELECT MAX(id) AS max FROM '.BS_TB_POSTS.'
			 WHERE rubrikid = '.$fid.$exclude
		);
		if(!$data)
			return 0;
		
		return $data['max'];
	}
	
	/**
	 * Determines the data of the last-post in the given topic. If you specify post-ids this ids will
	 * be excluded. You'll get the fields:
	 * <code>
	 * 	array(
	 * 		'id' => <postId>,
	 * 		'post_user' => <userId>,
	 * 		'post_an_user' => <guestName>,
	 * 		'post_time' => <postTime>
	 * 	)
	 * </code>
	 *
	 * @param int $id the id of the topic
	 * @param array $post_ids an numeric array with the post-ids to exclude
	 * @return array the last-post-data
	 */
	public function get_lastpost_data_in_topic($id,$post_ids = array())
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Array_Utils::is_integer($post_ids))
			FWS_Helper::def_error('intarray','post_ids',$post_ids);
		
		$exclude = count($post_ids) > 0 ? ' AND id NOT IN ('.implode(',',$post_ids).')' : '';
		$data = $db->get_row(
			'SELECT id,post_user,post_an_user,post_time
			 FROM '.BS_TB_POSTS.'
			 WHERE threadid = '.$id.$exclude.'
			 ORDER BY id DESC'
		);
		if(!$data)
			return array('id' => 0,'post_user' => 0,'post_an_user' => null,'post_time' => 0);
		
		return $data;
	}
	
	/**
	 * Returns the last posts, grouped by topic, of the given user
	 *
	 * @param int $uid the user-id
	 * @param array $excl_forums an array of forum-ids to exclude
	 * @param int $number the max. number of rows
	 * @return array the last posts
	 */
	public function get_last_posts_of_user($uid,$excl_forums = array(),$number = 5)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($uid) || $uid <= 0)
			FWS_Helper::def_error('intgt0','uid',$uid);
		if(!FWS_Array_Utils::is_integer($excl_forums))
			FWS_Helper::def_error('intarray','excl_forums',$excl_forums);
		if(!FWS_Helper::is_integer($number) || $number < 0)
			FWS_Helper::def_error('intge0','number',$number);
		
		return $db->get_rows(
			'SELECT p.*,t.name,MAX(p.id) AS id,MAX(p.post_time) AS post_time
			 FROM '.BS_TB_POSTS.' p
			 LEFT JOIN '.BS_TB_THREADS.' t ON p.threadid = t.id
			 WHERE p.post_user = '.$uid.'
			 '.(count($excl_forums) > 0 ? 'AND p.rubrikid NOT IN ('.implode(',',$excl_forums).')' : '').'
			 GROUP BY p.threadid
			 ORDER BY id DESC
			 '.($number > 0 ? 'LIMIT '.$number : '')
		);
	}
	
	/**
	 * Returns all posts that are invalid
	 *
	 * @return array the posts
	 */
	public function get_invalid_post_ids()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT id FROM '.BS_TB_POSTS.' WHERE text = "" AND text_posted != ""'
		);
	}
	
	/**
	 * The query for the statistics to get posts grouped by the date.
	 * You get the fields:
	 * <code>
	 * 	array(
	 * 		'post_time',
	 * 		'num' // the number of users
	 * 		'date', // the date as YYYYMM
	 *	)
	 * </code>
	 *
	 * @return array the posts
	 */
	public function get_post_stats_grouped_by_date()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT post_time,COUNT(id) num,
							CONCAT(YEAR(FROM_UNIXTIME(post_time)),MONTH(FROM_UNIXTIME(post_time))) date
			 FROM '.BS_TB_POSTS.'
			 GROUP BY date
			 ORDER BY id DESC'
		);
	}
	
	/**
	 * Creates a new post with the given fields
	 *
	 * @param array $fields all fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_POSTS,$fields);
	}
	
	/**
	 * Updates the given fields for the post with given id
	 *
	 * @param int $id the id of the post
	 * @param array $fields all fields to set
	 * @return int the number of affected rows
	 */
	public function update($id,$fields)
	{
		return $this->update_by_ids(array($id),$fields);
	}
	
	/**
	 * Updates the given fields for all posts with given ids
	 *
	 * @param array $ids an array of post-ids
	 * @param array $fields all fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_ids($ids,$fields)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->update(BS_TB_POSTS,'WHERE id IN ('.implode(',',$ids).')',$fields);
	}
	
	/**
	 * Updates the given fields for all posts from given topics
	 *
	 * @param array $tids an array of topic-ids
	 * @param array $fields all fields to set
	 * @return int the number of affected rows
	 */
	public function update_by_topics($tids,$fields)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($tids) || count($tids) == 0)
			FWS_Helper::def_error('intarray>0','tids',$tids);
		
		return $db->update(BS_TB_POSTS,'WHERE threadid IN ('.implode(',',$tids).')',$fields);
	}
	
	/**
	 * Assignes the given guest-name to the post with given user-id
	 *
	 * @param int $id the user-id
	 * @param string $user_name the guest-name to assign
	 * @return int the number of affected rows
	 */
	public function assign_posts_to_guest($id,$user_name)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_POSTS,'WHERE post_user = '.$id,array(
			'post_user' => 0,
			'post_an_user' => $user_name
		));
	}
	
	/**
	 * Deletes all posts with given ids
	 *
	 * @param array $ids all ids to delete
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		return $this->delete_by('id',$ids);
	}
	
	/**
	 * Deletes all posts with given topic-ids
	 *
	 * @param array $tids all topic-ids to delete
	 * @return int the number of affected rows
	 */
	public function delete_by_topics($tids)
	{
		return $this->delete_by('threadid',$tids);
	}
	
	/**
	 * Deletes all posts with given forum-ids
	 *
	 * @param array $fids all forum-ids to delete
	 * @return int the number of affected rows
	 */
	public function delete_by_forums($fids)
	{
		return $this->delete_by('rubrikid',$fids);
	}
	
	/**
	 * Deletes posts where the field's value is one of the given ids
	 *
	 * @param string $field the field-name
	 * @param array $ids the ids to delete
	 * @return int the number of affected rows
	 */
	protected function delete_by($field,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_POSTS.' WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>
