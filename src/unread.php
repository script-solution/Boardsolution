<?php
/**
 * Contains the unread-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * This class saves and manages the unread-data
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Unread extends PLIB_FullObject implements PLIB_Initable
{
	/**
	 * The unread threads:
	 * <code>
	 * 	array(
	 * 		<topicID> => array(<firstUnreadPost>,<forumID>),
	 * 		...
	 *	)
	 * </code>
	 *
	 * @var array
	 */
	private $_unread_threads = array();

	/**
	 * The unread forums:
	 * <code>
	 * 	array(
	 * 		<fid> => array(
	 * 			<tid> => array(
	 * 				<postid>,
	 * 				...
	 * 			),
	 * 			...
	 * 		),
	 * 		...
	 *	)
	 * </code>
	 *
	 * @var array
	 */
	private $_unread_forums = array();

	/**
	 * All unread news-topics:
	 * <code>
	 * 	array(<topicID> => <markReadInPortal>)
	 * </code>
	 *
	 * @var array
	 */	
	private $_unread_news = array();
	
	public function init()
	{
		if($this->user->is_loggedin())
		{
			foreach(BS_DAO::get_unread()->get_all_of_user($this->user->get_user_id()) as $data)
			{
				// is the post not available anymore?
				if($data['rubrikid'] == '')
					continue;
				
				if($data['is_news'])
					$this->_unread_news[$data['threadid']] = true;
				
				if(!isset($this->_unread_threads[$data['threadid']]))
					$this->_unread_threads[$data['threadid']] = array($data['post_id'],$data['rubrikid']);
				else if($data['post_id'] < $this->_unread_threads[$data['threadid']][0])
					$this->_unread_threads[$data['threadid']][0] = $data['post_id'];
				
				if(!isset($this->_unread_forums[$data['rubrikid']][$data['threadid']]))
					$this->_unread_forums[$data['rubrikid']][$data['threadid']] = array();
				$this->_unread_forums[$data['rubrikid']][$data['threadid']][] = $data['post_id'];
			}
		}
		
		$this->update_unread();
	}

	/**
	 * @return int the number of unread topics
	 */
	public function get_length()
	{
		return count($this->_unread_threads);
	}
	
	/**
	 * @return int the number of unread news
	 */
	public function get_unread_news_num()
	{
		return count($this->_unread_news);
	}
	
	/**
	 * @return array all unread forums as numeric array
	 */
	public function get_unread_forums()
	{
		return array_keys($this->_unread_forums);
	}

	/**
	 * returns the unread topics
	 *
	 * @return array the unread topics
	 */
	public function get_unread_topics()
	{
		return $this->_unread_threads;
	}

	/**
	 * @param int $tid the id of the topic
	 * @return int the post-id of the first unread post in the given thread
	 */
	public function get_first_unread_post($tid)
	{
		if(!isset($this->_unread_threads[$tid]))
			return -1;

		return $this->_unread_threads[$tid][0];
	}

	/**
	 * checks wether the given thread is unread
	 *
	 * @param int $id the id of the thread
	 * @return boolean true if the given thread is unread
	 */
	public function is_unread_thread($id)
	{
		return isset($this->_unread_threads[$id]);
	}
	
	/**
	 * Checks wether the forum with given id is unread
	 *
	 * @param int $id the forum-id
	 * @return boolean true if the forum is unread
	 */
	public function is_unread_forum($id)
	{
		return isset($this->_unread_forums[$id]);
	}
	
	/**
	 * Checks wether the given news (topic-id) is unread
	 * 
	 * @param int $id the news(topic)-id
	 * @return boolean true if unread
	 */
	public function is_unread_news($id)
	{
		return isset($this->_unread_news[$id]);
	}

	/**
	 * update the list of unread thread-ids and save it to session
	 */
	public function update_unread()
	{
		if(!$this->user->is_loggedin())
			return;
		
		$update = false;
		if($this->user->force_unread_update())
			$update = true;
		else if(!$this->input->isset_var(BS_URL_AT,'get') &&
				!$this->input->isset_var('action_type','post'))
			$update = true;

		if($update)
			$this->_create_list();
	}
	
	/**
	 * Marks all news read
	 */
	public function mark_news_read()
	{
		if($this->user->is_loggedin())
			BS_DAO::get_unread()->delete_news_of_user($this->user->get_user_id());
		
		$this->_unread_news = array();
	}

	/**
	 * marks the given topics unread
	 *
	 * @param array $ids an numeric array with the ids of the topics
	 */
	public function mark_topics_unread($ids)
	{
		// determine if we have to grab news
		$grab_news = $this->cfg['enable_portal_news'] && $this->cfg['enable_portal'];
		if($grab_news)
		{
			$news_fids = PLIB_Array_Utils::advanced_explode(',',$this->cfg['news_forums']);
			if(!PLIB_Array_Utils::is_integer($news_fids) || count($news_fids) == 0)
				$grab_news = false;
		}
		
		// gab all infos from the database and update the state of this object
		$post_ids = array();
		foreach(BS_DAO::get_posts()->get_first_post_from_topics($ids) as $data)
		{
			if(!isset($this->_unread_forums[$data['rubrikid']][$data['id']]))
				$this->_unread_forums[$data['rubrikid']][$data['id']] = array();
			
			if(!in_array($data['first_post'],$this->_unread_forums[$data['rubrikid']][$data['id']]))
			{
				$this->_unread_forums[$data['rubrikid']][$data['id']][] = $data['first_post'];
				
				$this->_unread_threads[$data['id']] = array($data['first_post'],$data['rubrikid']);
				$is_news = $grab_news && in_array($data['rubrikid'],$news_fids);
				if($is_news)
					$this->_unread_news[$data['id']] = true;
				
				$post_ids[$data['first_post']] = $is_news;
			}
		}
		
		// now insert the post-ids into the unread-table
		$this->_add_post_ids($post_ids);
	}

	/**
	 * marks the given topics read
	 *
	 * @param array $ids an numeric array with the ids of the topics
	 */
	public function mark_topics_read($ids)
	{
		if(count($ids) == 0)
			return;

		$post_ids = array();
		
		// remove topics
		foreach($ids as $id)
		{
			if(!isset($this->_unread_threads[$id]))
				continue;
			
			$fid = $this->_unread_threads[$id][1];
			unset($this->_unread_threads[$id]);
			
			// mark the news read, if existing
			if(isset($this->_unread_news[$id]))
				unset($this->_unread_news[$id]);
			
			// store post-ids and remove topic from the list
			$post_ids = array_merge($post_ids,$this->_unread_forums[$fid][$id]);
			unset($this->_unread_forums[$fid][$id]);
		}
		
		if(count($post_ids) > 0)
		{
			// determine the forums to remove
			foreach($this->_unread_forums as $fid => $tids)
			{
				if(count($tids) == 0)
					unset($this->_unread_forums[$fid]);
			}
			
			// delete from database
			$this->_remove_post_ids($post_ids);
		}
	}

	/**
	 * Marks the forum with given id read
	 *
	 * @param int $id the id of the forum
	 */
	public function mark_forum_read($id)
	{
		//  we'll mark all sub-forums read, too, if we don't show all subforums in the forums-view
		$data = $this->forums->get_node_data($id);
		if(!$data->get_display_subforums())
		{
			$sub_forums = $this->forums->get_sub_node_ids($id);
			$sub_forums[] = $id;
		}
		else
			$sub_forums = array($id);

		$post_ids = array();
		foreach($sub_forums as $fid)
		{
			// collect post-ids and remove topics and news
			foreach($this->_unread_forums[$fid] as $tid => $content)
			{
				unset($this->_unread_threads[$tid]);
				unset($this->_unread_news[$tid]);
				
				$post_ids = array_merge($post_ids,$content);
			}
			
			// remove forum
			unset($this->_unread_forums[$fid]);
		}
		
		$this->_remove_post_ids($post_ids);
	}

	/**
	 * Marks all topics read
	 */
	public function mark_all_read()
	{
		$this->_unread_threads = array();
		$this->_unread_forums = array();
		$this->_unread_news = array();
		$this->_lastpost_time = time();
		
		BS_DAO::get_unread()->delete_by_user($this->user->get_user_id());
	}

	// ---- PRIVATE METHODS ----

	/**
	 * Creates the list of the unread threads and calculates the interval
	 *
	 * @return boolean true if the unread has been updated
	 */
	private function _create_list()
	{
		$last_update = $this->user->get_profile_val('last_unread_update');
		$stats = $this->cache->get_cache('stats')->get_element(0,false);
		$last_post = max($stats['posts_last'],$stats['last_edit']);
	
		// nothing to do?
		if($last_post <= $last_update)
			return false;
		
		// select the NOT-favorite-forums for the current user
		$forum_ids = array();
		foreach(BS_DAO::get_unreadhide()->get_all_of_user($this->user->get_user_id()) as $data)
			$forum_ids[] = $data['forum_id'];

		// don't add denied topics to the unread-topics
		$excl_fids = array();
		if($this->cfg['hide_denied_forums'] == 1)
			$excl_fids = array_merge($forum_ids,BS_ForumUtils::get_instance()->get_denied_forums(false));
		
		// don't add already unread threads again (we would loose the first unread post-id)
		$excl_tids = array();
		if(count($this->_unread_threads) > 0)
			$excl_tids = array_keys($this->_unread_threads);

		// grab unread news from db?
		$grab_news = $this->cfg['enable_portal_news'] && $this->cfg['enable_portal'];
		if($grab_news)
		{
			$news_fids = PLIB_Array_Utils::advanced_explode(',',$this->cfg['news_forums']);
			if(!PLIB_Array_Utils::is_integer($news_fids) || count($news_fids) == 0)
				$grab_news = false;
		}

		// grab all posts since the last update from the database
		$post_ids = array();
		$topic_ids = array();
		$postlist = BS_DAO::get_posts()->get_unread_posts(
			$last_update,$this->user->get_user_id(),$excl_fids,$excl_tids,BS_MAX_UNREAD_TOPICS
		);
		foreach($postlist as $data)
		{
			if(!isset($this->_unread_forums[$data['rubrikid']][$data['threadid']]))
				$this->_unread_forums[$data['rubrikid']][$data['threadid']] = array();
			
			if(!in_array($data['first_unread_post'],
					$this->_unread_forums[$data['rubrikid']][$data['threadid']]))
			{
				$this->_unread_forums[$data['rubrikid']][$data['threadid']][] = $data['first_unread_post'];
				$this->_unread_threads[$data['threadid']] = array($data['first_unread_post'],$data['rubrikid']);
			
				if($grab_news && in_array($data['rubrikid'],$news_fids))
					$topic_ids[] = $data['threadid'];
				
				$post_ids[$data['first_unread_post']] = false;
			}
		}
		
		// TODO is here a bug that causes (sometimes) a duplicate row in the unread-table?
		
		// now determine which of the unread posts are unread news
		if($grab_news && count($topic_ids) > 0)
		{
			foreach(BS_DAO::get_posts()->get_first_post_from_topics($topic_ids) as $data)
			{
				if($data['first_post'] == $this->_unread_threads[$data['threadid']][0])
				{
					$this->_unread_news[$data['threadid']] = true;
					$post_ids[$data['first_post']] = true;
				}
			}
		}
		
		$this->_add_post_ids($post_ids);
		
		// we update the topics to the db at this point. Therefore we don't set _unread_update to true.
		$now = time();
		BS_DAO::get_profile()->update_user_by_id(
			array('last_unread_update' => $now),$this->user->get_user_id()
		);
		$this->user->set_profile_val('last_unread_update',$now);

		return true;
	}
	
	/**
	 * Adds the given post-ids to the unread posts
	 *
	 * @param array $ids an array of the form: <code>array(<postID> => <isNews>,...)</code>
	 */
	private function _add_post_ids($ids)
	{
		if(count($ids) == 0)
			return;
		
		BS_DAO::get_unread()->create($this->user->get_user_id(),$ids);
	}
	
	/**
	 * Removes the given post-ids from the database
	 *
	 * @param array $ids the post-ids
	 */
	private function _remove_post_ids($ids)
	{
		if(count($ids) == 0)
			return;
		
		BS_DAO::get_unread()->delete_posts_of_user($this->user->get_user_id(),$ids);
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>