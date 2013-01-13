<?php
/**
 * Contains the event-class
 * 
 * @package			Boardsolution
 * @subpackage	src
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
 * This class is used to display the announcements for an event
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Event extends FWS_Object
{
	/**
	 * The event-data
	 *
	 * @var array
	 */
	private $_event;
	
	/**
	 * An array with all users that are announced
	 *
	 * @var array
	 */
	private $_ann;
	
	/**
	 * Wether the current user is announced
	 *
	 * @var boolean
	 */
	private $_is_announced = false;
	
	/**
	 * Constructor
	 *
	 * @param array $event the event-data
	 */
	public function __construct($event)
	{
		$user = FWS_Props::get()->user();
		
		if(!is_array($event))
			FWS_Helper::def_error('array','event',$event);
		
		parent::__construct();
		
		$this->_event = $event;
		$this->_ann = BS_DAO::get_eventann()->get_user_of_event($event['id']);
		
		$uid = $user->get_user_id();
		foreach($this->_ann as $userdata)
		{
			if($uid == $userdata['user_id'])
			{
				$this->_is_announced = true;
				break;
			}
		}
	}
	
	/**
	 * @return int the number of announcements
	 */
	public function get_count()
	{
		return count($this->_ann);
	}
	
	/**
	 * Builds a list with all announced users
	 *
	 * @param boolean $use_links wether links should be used for the user-names
	 * @return string the list
	 */
	public function get_announcement_list($use_links = true)
	{
		$list = '';
		$len = count($this->_ann);
		if($len > 0)
		{
			$i = 0;
			foreach($this->_ann as $user)
			{
				if($use_links)
				{
					$list .= BS_UserUtils::get_link(
						$user['user_id'],$user['user_name'],$user['user_group']
					);
				}
				else
					$list .= $user['user_name'];
				
				if($i < $len - 1)
					$list .= ', ';
			}
		}
		else
			$list = '-';
		
		return $list;
	}
	
	/**
	 * Checks wether the current user is announced to this event
	 *
	 * @return boolean true if so
	 */
	public function is_announced()
	{
		return $this->_is_announced;
	}
	
	/**
	 * Checks wether the current event is open for announcements or leaves (for the current user)
	 *
	 * @return boolean true if so
	 */
	public function is_open()
	{
		$user = FWS_Props::get()->user();

		if(!$user->is_loggedin())
			return false;
		
		if($this->_event['tid'] > 0)
		{
			$tdata = BS_Front_TopicFactory::get_current_topic();
			if($tdata === null || $tdata['thread_closed'] == 1)
				return false;
		}
		
		$timeout = ($this->_event['timeout'] == 0) ? $this->_event['event_begin'] : $this->_event['timeout'];
		return $timeout > time();
	}
	
	/**
	 * @return boolean wether the current user can leave this event
	 */
	public function can_leave()
	{
		if(!$this->is_open())
			return false;
		
		return $this->is_announced();
	}
	
	/**
	 * @return boolean wether the current user can announce to this event
	 */
	public function can_announce()
	{
		if(!$this->is_open())
			return false;
		
		if(!$this->is_announced())
		{
			return $this->_event['max_announcements'] == 0 ||
				count($this->_ann) < $this->_event['max_announcements'];
		}
		
		return false;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>