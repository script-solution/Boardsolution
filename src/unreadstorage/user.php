<?php
/**
 * Contains the user-unread-storage-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.unreadstorage
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The unread-storage-implementation for registered and loggedin users
 * 
 * @package			Boardsolution
 * @subpackage	src.unreadstorage
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_UnreadStorage_User extends PLIB_FullObject implements BS_UnreadStorage
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
		foreach(BS_DAO::get_unread()->get_all_of_user($this->user->get_user_id()) as $data)
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
		return $this->user->get_profile_val('last_unread_update');
	}

	/**
	 * @see BS_UnreadStorage::set_last_update()
	 *
	 * @param int $time
	 */
	public function set_last_update($time)
	{
		BS_DAO::get_profile()->update_user_by_id(
			array('last_unread_update' => $time),$this->user->get_user_id()
		);
		$this->user->set_profile_val('last_unread_update',$time);
	}

	/**
	 * @see BS_UnreadStorage::add_post_ids()
	 *
	 * @param array $ids
	 */
	public function add_post_ids($ids)
	{
		BS_DAO::get_unread()->create($this->user->get_user_id(),$ids);
	}

	/**
	 * @see BS_UnreadStorage::remove_post_ids()
	 *
	 * @param array $ids
	 */
	public function remove_post_ids($ids)
	{
		BS_DAO::get_unread()->delete_posts_of_user($this->user->get_user_id(),$ids);
	}

	/**
	 * @see BS_UnreadStorage::remove_all()
	 */
	public function remove_all()
	{
		BS_DAO::get_unread()->delete_by_user($this->user->get_user_id());
	}
	
	/**
	 * @see BS_UnreadStorage::remove_all_news()
	 */
	public function remove_all_news()
	{
		BS_DAO::get_unread()->delete_news_of_user($this->user->get_user_id());
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>