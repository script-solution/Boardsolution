<?php
/**
 * Contains the guest-unread-storage-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.unreadstorage
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The unread-storage-implementation for guests
 * 
 * @package			Boardsolution
 * @subpackage	src.unreadstorage
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_UnreadStorage_Guest extends FWS_Object implements BS_UnreadStorage
{
	/**
	 * The unread-data
	 *
	 * @var array
	 */
	private $_data = array();
	
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
		$cookies = FWS_Props::get()->cookies();

		$sdata = $cookies->get_cookie('unread',FWS_Input::STRING);
		if(!$sdata)
			return;
		
		$temp = explode(',',$sdata);
		if(!is_array($temp))
			return;
		
		$this->_data = array();
		foreach($temp as $pid)
		{
			if($pid[0] == 'n')
			{
				$pid = (int)substr($pid,1);
				if($pid >= 1)
					$this->_data[$pid] = true;
			}
			else
			{
				$pid = (int)$pid;
				if($pid >= 1)
					$this->_data[$pid] = false;
			}
		}
		
		if(count($this->_data) == 0)
			return;
		
		foreach(BS_DAO::get_posts()->get_posts_by_ids(array_keys($this->_data)) as $data)
		{
			// is the post not available anymore?
			if($data['rubrikid'] == '')
				continue;
			
			if($this->_data[$data['id']])
				$this->_news[$data['threadid']] = true;
			
			if(!isset($this->_topics[$data['threadid']]))
				$this->_topics[$data['threadid']] = array($data['id'],$data['rubrikid']);
			else if($data['id'] < $this->_topics[$data['threadid']][0])
				$this->_topics[$data['threadid']][0] = $data['id'];
			
			if(!isset($this->_forums[$data['rubrikid']][$data['threadid']]))
				$this->_forums[$data['rubrikid']][$data['threadid']] = array();
			$this->_forums[$data['rubrikid']][$data['threadid']][] = $data['id'];
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
		$cookies = FWS_Props::get()->cookies();

		$time = $cookies->get_cookie('unread_update',FWS_Input::STRING);
		if($time === null || $time <= 0)
		{
			$time = time();
			$this->set_last_update($time);
		}
		return $time;
	}

	/**
	 * @see BS_UnreadStorage::set_last_update()
	 *
	 * @param int $time
	 */
	public function set_last_update($time)
	{
		$cookies = FWS_Props::get()->cookies();

		$cookies->set_cookie('unread_update',$time);
	}

	/**
	 * @see BS_UnreadStorage::add_post_ids()
	 *
	 * @param array $ids
	 */
	public function add_post_ids($ids)
	{
		$changed = false;
		foreach($ids as $id => $is_news)
		{
			$this->_data[$id] = $is_news;
			$changed = true;
		}
		
		if($changed)
			$this->_store();
	}

	/**
	 * @see BS_UnreadStorage::remove_post_ids()
	 *
	 * @param array $ids
	 */
	public function remove_post_ids($ids)
	{
		$changed = false;
		foreach($ids as $id)
		{
			$changed |= isset($this->_data[$id]);
			unset($this->_data[$id]);
		}
		
		if($changed)
			$this->_store();
	}

	/**
	 * @see BS_UnreadStorage::remove_all()
	 */
	public function remove_all()
	{
		$changed = count($this->_data) > 0;
		$this->_data = array();
		if($changed)
			$this->_store();
	}

	/**
	 * @see BS_UnreadStorage::remove_all_news()
	 */
	public function remove_all_news()
	{
		$changed = false;
		foreach($this->_data as $k => $v)
		{
			if($v)
			{
				unset($this->_data[$k]);
				$changed = true;
			}
		}
		
		if($changed)
			$this->_store();
	}
	
	/**
	 * Stores the current unread data
	 */
	private function _store()
	{
		$cookies = FWS_Props::get()->cookies();

		$d = array();
		foreach($this->_data as $pid => $is_news)
		{
			if($is_news)
				$d[] = 'n'.$pid;
			else
				$d[] = $pid;
		}
		$cookies->set_cookie('unread',implode(',',$d));
	}

	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>