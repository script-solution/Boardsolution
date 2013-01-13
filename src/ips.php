<?php
/**
 * Contains the ip-class
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
 * A class which makes it easier to log an action and check if the user has performed an action
 * in a specific interval
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_IPs extends FWS_Object
{
	/**
	 * Returns the data of the entry of given action (and the current user)
	 * 
	 * @param string $action the action-name
	 * @return array|bool the data of the entry or false if it failed
	 */
	public function get_entry($action)
	{
		$user = FWS_Props::get()->user();

		$name = strtok($action,'_');
		$timeout = $this->get_timeout($name);
		$ip = $user->get_user_ip();
		return BS_DAO::get_logips()->get_by_action_for_ip($action,$ip,$timeout);
	}
	
	/**
	 * Checks wether an entry for the given action exists
	 * 
	 * @param string $action the action-name
	 * @return bool true if so
	 */
	public function entry_exists($action)
	{
		return $this->get_entry($action) !== false;
	}
	
	/**
	 * Adds an entry for the given action in the ip-log-table
	 * 
	 * @param string $action the action to store
	 */
	public function add_entry($action)
	{
		BS_DAO::get_logips()->create($action);
	}
	
	/**
	 * Returns the timeout for the given action
	 * 
	 * @param string $action the action your looking for
	 * @return int the timeout (default 0)
	 */
	public function get_timeout($action)
	{
		$cfg = FWS_Props::get()->cfg();

		switch($action)
		{
			case 'post':
				if($cfg['spam_post'] > 0)
					return $cfg['spam_post'];
				break;
			
			case 'topic':
				if($cfg['spam_thread'] > 0)
					return $cfg['spam_thread'];
				break;
				
			case 'pm':
				if($cfg['spam_pm'] > 0)
					return $cfg['spam_pm'];
				break;
			
			case 'reg':
				if($cfg['spam_reg'] > 0)
					return $cfg['spam_reg'];
				break;
			
			case 'mail':
				if($cfg['spam_email'] > 0)
					return $cfg['spam_email'];
				break;
			
			case 'linkadd':
				if($cfg['spam_linkadd'] > 0)
					return $cfg['spam_linkadd'];
				break;
			
			case 'linkre':
				if($cfg['spam_linkview'] > 0)
					return $cfg['spam_linkview'];
				break;
			
			case 'search':
				if($cfg['spam_search'] > 0)
					return $cfg['spam_search'];
				break;
			
			case 'adl':
				return 3600;
		}
		
		return 0;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>