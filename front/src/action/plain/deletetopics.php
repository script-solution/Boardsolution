<?php
/**
 * Contains the plain-delete-topics-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The plain-action to delete topics
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Plain_DeleteTopics extends BS_Front_Action_Plain
{
	/**
	 * All ids to delete
	 *
	 * @var array
	 */
	private $_ids;
	
	/**
	 * The forum-id in which the topics are
	 *
	 * @var int
	 */
	private $_fid;
	
	/**
	 * Wether we should adjust the user-experience
	 *
	 * @var int
	 */
	private $_adjust_user_exp;
	
	/**
	 * Wether we should adjust the forum-attributes
	 *
	 * @var int
	 */
	private $_adjust_forum_attr;
	
	/**
	 * Constructor<br>
	 * WARNING: If you disable <var>$adjust_user_exp</var> or <var>$adjust_forum_attr</var> you
	 * HAVE TO do this manually! Otherwise the database-structure will become inconsistent!
	 * 
	 * @param array $ids the topic-ids
	 * @param int $fid the forum-id
	 * @param boolean $adjust_user_exp adjust the experience and posts of the users?
	 * @param boolean $adjust_forum_attr adjust the forum-attributes?
	 */
	public function __construct($ids,$fid,$adjust_user_exp = true,$adjust_forum_attr = true)
	{
		parent::__construct();
		
		$this->_ids = $ids;
		$this->_fid = (int)$fid;
		$this->_adjust_user_exp = (bool)$adjust_user_exp;
		$this->_adjust_forum_attr = (bool)$adjust_forum_attr;
	}
	
	public function check_data()
	{
		// check if the ids are valid
		if(!is_array($this->_ids) || count($this->_ids) == 0)
			return 'Got invalid topic-ids';
		
		if($this->_fid <= 0)
			return 'Invalid forum-id "'.$this->_fid.'"';
		
		return parent::check_data();
	}
	
	public function perform_action()
	{
		$db = FWS_Props::get()->db();
		$forums = FWS_Props::get()->forums();
		$functions = FWS_Props::get()->functions();

		parent::perform_action();
		
		$db->start_transaction();

		$forum_data = $forums->get_node_data($this->_fid);
	
		// collect some values
		$refresh_last_post = false;
		$total_posts = 0;
		$topics = array();
		$default_topics = array();
		$shadow_topics = array();
		$user_experience = array();
		$poll_ids = array();
		$event_ids = array();
		foreach(BS_DAO::get_topics()->get_by_ids($this->_ids,$this->_fid) as $data)
		{
			$topics[] = $data['id'];
	
			// is it a shadow-topic?
			if($data['moved_tid'] > 0)
				$shadow_topics[] = $data['id'];
			else
			{
				$default_topics[] = $data['id'];
	
				if($this->_adjust_user_exp)
				{
					if(!isset($user_experience[$data['post_user']]))
						$user_experience[$data['post_user']] = array('experience' => 0,'posts' => 0);
					
					if($forum_data->get_increase_experience() == 1)
						$user_experience[$data['post_user']]['experience'] += BS_EXPERIENCE_FOR_TOPIC;
				}
	
				// + 1 because we store the number of replies in the db, not the number of posts
				$total_posts += $data['posts'] + 1;
	
				if($forum_data->get_lastpost_id() == $data['lastpost_id'])
					$refresh_last_post = true;
	
				if($data['type'] > 0)
					$poll_ids[] = $data['type'];
				else if($data['type'] == -1)
					$event_ids[] = $data['id'];
			}
		}
		
		$total_topics = count($topics);
		$total_default_topics = count($default_topics);
		$total_shadow_topics = count($shadow_topics);
		if($total_default_topics > 0 || $total_shadow_topics > 0)
		{
			$max_post_time = 0;
	
			// find shadow-topics for the default topics
			if($total_default_topics > 0)
			{
				$shadow_reduce_forums = array();
				foreach(BS_DAO::get_topics()->get_by_moved_ids($default_topics) as $sdata)
				{
					if(isset($shadow_reduce_forums[$sdata['rubrikid']]))
						$shadow_reduce_forums[$sdata['rubrikid']]++;
					else
						$shadow_reduce_forums[$sdata['rubrikid']] = 1;
				}
	
				if(count($shadow_reduce_forums) > 0)
				{
					// reduce the number of topics for the shadow-topics
					foreach($shadow_reduce_forums as $forum_id => $number)
					{
						BS_DAO::get_forums()->update_by_id($forum_id,array(
							'threads' => array('threads - '.$number)
						));
						$total_topics += $number;
					}
	
					// delete the shadow-topics
					BS_DAO::get_topics()->delete_shadow_topics($default_topics);
				}
	
				// delete subscriptions
				BS_DAO::get_subscr()->delete_by_topics($default_topics);
	
				// calculate the number of posts in the topics from today and yesterday
				// to update the stats-table
				foreach(BS_DAO::get_posts()->get_posts_by_topics($default_topics) as $pdata)
				{
					if($pdata['post_time'] > $max_post_time)
						$max_post_time = $pdata['post_time'];
	
					if($this->_adjust_user_exp)
					{
						if(!isset($user_experience[$pdata['post_user']]['experience']))
							$user_experience[$pdata['post_user']]['experience'] = 0;
						if($forum_data->get_increase_experience())
							$user_experience[$pdata['post_user']]['experience'] += BS_EXPERIENCE_FOR_POST;
		
						if(isset($user_experience[$pdata['post_user']]['posts']))
							$user_experience[$pdata['post_user']]['posts']++;
						else
							$user_experience[$pdata['post_user']]['posts'] = 1;
					}
				}
	
				// decrease the user-experience
				foreach($user_experience as $user_id => $content)
				{
					if(!isset($content['posts']))
						$content['posts'] = 0;
					
					BS_DAO::get_profile()->update_user_by_id(array(
						'posts' => array('posts - '.$content['posts']),
						'exppoints' => array('exppoints - '.$content['experience'])
					),$user_id);
				}
			}
			
			// we have to do this here because the topics have to exist!
			BS_UnreadUtils::get_instance()->remove_topics($default_topics);
	
			// delete topics and posts
			if($total_default_topics > 0)
				BS_DAO::get_posts()->delete_by_topics($default_topics);
	
			BS_DAO::get_topics()->delete_by_ids($topics);
	
			BS_Front_Action_Helper::get_instance()->adjust_last_post_time($max_post_time);
	
			// update the forum
			if($this->_adjust_forum_attr)
			{
				$fields = array(
					'posts' => array('posts - '.$total_posts),
					'threads' => array('threads - '.$total_topics)
				);
				
				if($refresh_last_post)
				{
					$flastpost = BS_DAO::get_posts()->get_lastpost_id_in_forum($this->_fid);
					$fields['lastpost_id'] = $flastpost;
				}
				
				BS_DAO::get_forums()->update_by_id($this->_fid,$fields);
			}
	
			// delete polls and votes
			if(count($poll_ids) > 0)
			{
				BS_DAO::get_polls()->delete_by_ids($poll_ids);
				BS_DAO::get_pollvotes()->delete_by_polls($poll_ids);
			}
	
			// delete events
			if(count($event_ids) > 0)
			{
				// delete the events before we remove them in the cache!
				BS_DAO::get_events()->delete_by_topicids($event_ids);
				BS_DAO::get_eventann()->delete_by_events($event_ids);
			}
	
			// delete attachments from the uploads-dir
			if($total_default_topics > 0)
			{
				foreach(BS_DAO::get_attachments()->get_by_topicids($default_topics) as $adata)
					$functions->delete_attachment($adata['attachment_path']);
	
				// delete attachments in the db
				BS_DAO::get_attachments()->delete_by_topicids($default_topics);
			}
		}
		
		$db->commit_transaction();
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>