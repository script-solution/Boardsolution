<?php
/**
 * Contains the session-class
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
 * This class manages all currently online user. It uses a storage-object
 * to support different storage-locations for the data.
 *
 * @package			Boardsolution
 * @subpackage	src.session
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Session_Manager extends FWS_Session_Manager
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(new BS_Session_Storage_DB());
	}
	
	protected function check_online_timeout($user,$currentsid)
	{
		// We want to treat acp-users different from frontend-users
		$loc = $user->get_location();
		$timeout = FWS_String::starts_with($loc,'acp:') ? BS_ACP_ONLINE_TIMEOUT : BS_ONLINE_TIMEOUT;
		return $user->get_date() < (time() - $timeout) && $user->get_session_id() != $currentsid;
	}
	
	/**
	 * determines the location of the user with given id
	 *
	 * @param int $user_id the id of the user
	 * @return string the location or an empty string if the user is offline
	 */
	public function get_user_location($user_id)
	{
		static $user_locs = null;
		if($user_locs === null)
		{
			$user_locs = array();
			foreach($this->get_online_list() as $user)
				$user_locs[$user->get_user_id()] = $user->get_location();
		}

		return isset($user_locs[$user_id]) ? $user_locs[$user_id] : '';
	}
	
	/**
	 * collects all user who are currently online at the given location
	 *
	 * @param string $location the type of the location: all, forums, topics, posts
	 * @param int $id topics: the id of the forum; posts: the id of the topic
	 * @param boolean $hide_duplicates do you want to group all registered and bots to one user?
	 * @return array an numeric array with the data of the user who are online at the given position:
	 * 	<code>
	 * 		array(
	 * 			'session_id` => <sessionID>,
	 * 			'user_id' => <userID>, // 0 = guest
	 * 			'user_ip' => <userIP>,
	 * 			'date' => <timeStampOfLastClick>,
	 * 			'location` => <location>,
	 * 			'user_agent' => <userAgent>,
	 * 			'session_data' => <sessionData>, // serialized
	 * 			'bot_name' => <botName>, // the name of the bot, if it is one
	 * 			'duplicates' => <duplicateCount> // just if $hide_duplicates is false
	 * 		)
	 * 	</code>
	 */
	public function get_user_at_location($location = 'forums',$id = -1,$hide_duplicates = true)
	{
		$forums = FWS_Props::get()->forums();

		$bots = array();
		$user = array();
		$guests = array();
		foreach($this->get_online_list() as $u)
		{
			/* @var $u BS_Session_Data */
			
			// skip admin-locations in the board
			if($location != 'all' && FWS_String::substr($u->get_location(),0,4) == 'acp:')
				continue;
			
			$add = false;
			switch($location)
			{
				case 'all':
				case 'forums':
					$add = true;
					break;
				
				case 'topics':
					$parts = explode(':',$u->get_location());
					if(!isset($parts[1]) || $parts[0] == 'userdetails')
						break;
					
					if($parts[1] == $id)
					{
						$add = true;
						break;
					}
					
					// look if the user is in a subforum
					if($forums->has_childs($id))
					{
						$sub = $forums->get_sub_nodes($id);
						$len = count($sub);
						for($i = 0;$i < $len;$i++)
						{
							if($sub[$i]->get_id() == $parts[1])
							{
								$add = true;
								break;
							}
						}
					}
					break;
				
				case 'posts':
					$parts = explode(':',$u->get_location());
					if(isset($parts[2]) && $parts[2] == $id)
						$add = true;
					break;
			}
			
			// continue with the next one if we don't want to add the user
			if(!$add)
				continue;
			
			// determine type and identification
			$uid = -1;
			if($u->is_bot())
			{
				$type = 'bots';
				$uid = $u->get_bot_name();
			}
			else if($u->is_loggedin())
			{
				$type = 'user';
				$uid = $u->get_user_id();
			}
			else
				$type = 'guests';
			
			// add the current user
			if(!$hide_duplicates || $type == 'guests' || !isset(${$type}[$uid]))
			{
				$entry = $u->get_attributes();
				$entry['duplicates'] = 0;
				
				switch($type)
				{
					case 'bots':
						if($hide_duplicates)
							$bots[$uid] = $entry;
						else
							$bots[] = $entry;
						break;
					case 'user':
						if($hide_duplicates)
							$user[$uid] = $entry;
						else
							$user[] = $entry;
						break;
					case 'guests':
						$guests[] = $entry;
						break;
				}
			}
			// increase the duplicates
			else if($hide_duplicates && $type != 'guests')
				${$type}[$uid]['duplicates']++;
		}
		
		return array_merge(array_values($user),array_values($bots),$guests);
	}

	/**
	 * determines the last login
	 *
	 * @return array an array of the form:
	 * 	<code>
	 * 		array(
	 * 			'user_name' => ...,
	 * 			'lastlogin' => ...,
	 * 			'id' => ...,
	 * 			'user_group' => ...
	 *		)
	 * 	</code>
	 */
	public function get_last_login()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();

		// at first we look in the session-table (sorted by date descending)
		$last = null;
		foreach($this->get_online_list() as $suser)
		{
			// the user has to be loggedin and we don't want to get ourself :)
			if($suser->get_user_id() <= 0 || $suser->get_user_id() == $user->get_user_id())
				continue;
			
			// is the user a ghost?
			if(!$user->is_admin() && $suser->get_ghost_mode() == 1 &&
					$cfg['allow_ghost_mode'] == 1)
				continue;

			// we have found a valid entry
			$last = array(
				'user_name' => $suser->get_user_name(),
				'lastlogin' => $suser->get_date(),
				'id' => $suser->get_user_id(),
				'user_group' => $suser->get_user_group()
			);
			break;
		}

		// no other loggedin user in the session-table?
		// so we have to search for the last loggedin user in the profile-table
		if($last === null)
			$last = BS_DAO::get_profile()->get_last_active_user();

		return $last;
	}
	
	protected function get_dump_vars()
	{
		return array_merge(parent::get_dump_vars(),get_object_vars($this));
	}
}
?>
