<?php
/**
 * Contains the user-unread-storage-class
 * 
 * @package			Boardsolution
 * @subpackage	src.unreadstorage
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
 * The unread-storage-implementation for registered and loggedin users
 * 
 * @package			Boardsolution
 * @subpackage	src.unreadstorage
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_UnreadStorage_User extends FWS_Object implements BS_UnreadStorage
{
	/**
	 * The unread forums
	 *
	 * @var array
	 */
	private $_forums = array();
	
	/**
	 * The unread topics
	 * 
	 * @var array
	 */
	private $_topics = array();
	
	/**
	 * The unread news
	 *
	 * @var array
	 */
	private $_news = array();
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$user = FWS_Props::get()->user();

		foreach(BS_DAO::get_unread()->get_all_of_user($user->get_user_id()) as $data)
		{
			// is the post not available anymore?
			if($data['rubrikid'] == '')
				continue;
			
			if($data['is_news'])
				$this->_news[$data['threadid']] = true;
			
			if(!isset($this->_topics[$data['threadid']]))
				$this->_topics[$data['threadid']] = array($data['post_id'],$data['rubrikid']);
			else if($data['post_id'] < $this->_topics[$data['threadid']][0])
				$this->_topics[$data['threadid']][0] = $data['post_id'];
			
			if(!isset($this->_forums[$data['rubrikid']][$data['threadid']]))
				$this->_forums[$data['rubrikid']][$data['threadid']] = array();
			$this->_forums[$data['rubrikid']][$data['threadid']][] = $data['post_id'];
		}
	}

	/**
	 * @see BS_UnreadStorage::get_forums()
	 *
	 * @return array
	 */
	public function get_forums()
	{
		return $this->_forums;
	}

	/**
	 * @see BS_UnreadStorage::get_news()
	 *
	 * @return array
	 */
	public function get_news()
	{
		return $this->_news;
	}

	/**
	 * @see BS_UnreadStorage::get_topics()
	 *
	 * @return array
	 */
	public function get_topics()
	{
		return $this->_topics;
	}

	/**
	 * @see BS_UnreadStorage::get_last_update()
	 *
	 * @return int
	 */
	public function get_last_update()
	{
		$user = FWS_Props::get()->user();

		return (int)$user->get_profile_val('last_unread_update');
	}

	/**
	 * @see BS_UnreadStorage::set_last_update()
	 *
	 * @param int $time
	 */
	public function set_last_update($time)
	{
		$user = FWS_Props::get()->user();

		BS_DAO::get_profile()->update_user_by_id(
			array('last_unread_update' => $time),$user->get_user_id()
		);
		$user->set_profile_val('last_unread_update',$time);
	}

	/**
	 * @see BS_UnreadStorage::add_post_ids()
	 *
	 * @param array $ids
	 */
	public function add_post_ids($ids)
	{
		$user = FWS_Props::get()->user();

		// remove post-ids that we already have (userid-postid is the PK in the db!)
		foreach($this->_forums as $topics)
		{
			foreach($topics as $posts)
			{
				foreach($posts as $pid)
				{
					if(isset($ids[$pid]))
						unset($ids[$pid]);
				}
			}
		}
		
		BS_DAO::get_unread()->create($user->get_user_id(),$ids);
	}

	/**
	 * @see BS_UnreadStorage::remove_post_ids()
	 *
	 * @param array $ids
	 */
	public function remove_post_ids($ids)
	{
		$user = FWS_Props::get()->user();

		BS_DAO::get_unread()->delete_posts_of_user($user->get_user_id(),$ids);
	}

	/**
	 * @see BS_UnreadStorage::remove_all()
	 */
	public function remove_all()
	{
		$user = FWS_Props::get()->user();

		BS_DAO::get_unread()->delete_by_user($user->get_user_id());
	}
	
	/**
	 * @see BS_UnreadStorage::remove_all_news()
	 */
	public function remove_all_news()
	{
		$user = FWS_Props::get()->user();

		BS_DAO::get_unread()->delete_news_of_user($user->get_user_id());
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>