<?php
/**
 * Contains the session-data-class
 * 
 * @package			Boardsolution
 * @subpackage	src.session
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
 * This class contains some additional information about the online users
 * for Boardsolution.
 *
 * @package			Boardsolution
 * @subpackage	src.session
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Session_Data extends FWS_Session_Data
{
	/**
	 * The location of the user
	 *
	 * @var string
	 */
	private $_location;
	
	/**
	 * Contains the name of the bot, if the user is one
	 *
	 * @var int|string
	 */
	private $_bot_name = -1;
	
	/**
	 * Is the ghost-mode enabled?
	 *
	 * @var int
	 */
	private $_ghost_mode;
	
	/**
	 * A comma-separated string with all user-groups
	 *
	 * @var string
	 */
	private $_user_group;
	
	/**
	 * Constructor
	 * 
	 * @param array $data the data from the database
	 */
	public function __construct($data)
	{
		parent::__construct(
			$data['session_id'],$data['user_id'],$data['user_ip'],
			$data['user_name'],$data['date'],$data['user_agent'],$data['session_data']
		);
		
		$this->_location = $data['location'];
		$this->_ghost_mode = $data['ghost_mode'];
		$this->_user_group = $data['user_group'];
	}
	
	/**
	 * Creates an associative array with all attributes of this user
	 *
	 * @return array an array with the attributes
	 */
	public function get_attributes()
	{
		return array(
			'session_id' => $this->get_session_id(),
			'user_id' => $this->get_user_id(),
			'user_ip' => $this->get_user_ip(),
			'user_name' => $this->get_user_name(),
			'user_agent' => $this->get_user_agent(),
			'date' => $this->get_date(),
			'session_data' => $this->get_session_data(),
			'location' => $this->get_location(),
			'bot_name' => $this->get_bot_name(),
			'ghost_mode' => $this->get_ghost_mode(),
			'user_group' => $this->get_user_group()
		);
	}
	
	/**
	 * @return boolean wether this user is a bot
	 * @see get_bot_name()
	 */
	public function is_bot()
	{
		// initialize botname, if necessary
		if($this->_bot_name === -1)
			$this->_bot_name = $this->_get_bot_name($this->get_user_agent(),$this->get_user_ip());
		
		return $this->_bot_name != null;
	}

	/**
	 * @return string the name of the bot or null if it is no bot
	 * @see is_bot()
	 */
	public function get_bot_name()
	{
		// initialize botname, if necessary
		if($this->_bot_name === -1)
			$this->_bot_name = $this->_get_bot_name($this->get_user_agent(),$this->get_user_ip());
		
		return (string)$this->_bot_name;
	}

	/**
	 * @return string the location of the user
	 */
	public function get_location()
	{
		return $this->_location;
	}

	/**
	 * @return int is the ghost-mode enabled?
	 */
	public function get_ghost_mode()
	{
		return $this->_ghost_mode;
	}
	
	/**
	 * Sets wether the ghost-mode is enabled
	 *
	 * @param boolean $ghost_mode the new value
	 */
	public function set_ghost_mode($ghost_mode)
	{
		$this->_ghost_mode = $ghost_mode ? 1 : 0;
	}

	/**
	 * @return string a comma-separated string with all user-groups
	 */
	public function get_user_group()
	{
		return $this->_user_group;
	}
	
	/**
	 * Sets the user-group to given value
	 *
	 * @param string $group the new value
	 */
	public function set_user_group($group)
	{
		if(empty($group) || !is_string($group))
			FWS_Helper::def_error('notempty','group',$group);
		
		$this->_user_group = $group;
	}

	/**
	 * Sets the location of the user
	 * 
	 * @param string $location the new value
	 */
	public function set_location($location)
	{
		if(!is_string($location))
			FWS_Helper::def_error('string','location',$location);

		$this->_has_changed |= $location != $this->_location;
		$this->_location = $location;
	}

	/**
	 * checks wether this user is a known bot and returns the name if so
	 *
	 * @param string $agent the user-agent
	 * @param string $ip the ip of the user
	 * @return string the bot-name or null if this user is no bot
	 */
	private function _get_bot_name($agent,$ip)
	{
		$cache = FWS_Props::get()->cache();

		foreach($cache->get_cache('bots') as $bot)
		{
			if(FWS_String::strpos($agent,$bot['bot_match']) !== false)
			{
				// is a ip-range given?
				if($bot['bot_ip_start'] != '')
				{
					if($ip >= $bot['bot_ip_start'] && $ip <= $bot['bot_ip_end'])
						return $bot['bot_name'];
					
					// otherwise we don't treat this user as a bot
					continue;
				}
				
				return $bot['bot_name'];
			}
		}

		return null;
	}
	
	protected function get_dump_vars()
	{
		return array_merge(parent::get_dump_vars(),get_object_vars($this));
	}
}
?>