<?php
/**
 * Contains the ip-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * A class which makes it easier to log an action and check if the user has performed an action
 * in a specific interval
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_IPs extends PLIB_FullObject
{
	/**
	 * Returns the data of the entry of given action (and the current user)
	 * 
	 * @param string $action the action-name
	 * @return array the data of the entry
	 */
	public function get_entry($action)
	{
		$name = strtok($action,'_');
		$timeout = $this->get_timeout($name);
		$ip = $this->user->get_user_ip();
		return BS_DAO::get_logips()->get_by_action_for_ip($action,$ip,$timeout);
	}
	
	/**
	 * Checks wether an entry for the given action exists
	 * 
	 * @param string $action the action-name
	 * @return true if so
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
		switch($action)
		{
			case 'post':
				if($this->cfg['spam_post'] > 0)
					return $this->cfg['spam_post'];
				break;
			
			case 'topic':
				if($this->cfg['spam_thread'] > 0)
					return $this->cfg['spam_thread'];
				break;
				
			case 'pm':
				if($this->cfg['spam_pm'] > 0)
					return $this->cfg['spam_pm'];
				break;
			
			case 'reg':
				if($this->cfg['spam_reg'] > 0)
					return $this->cfg['spam_reg'];
				break;
			
			case 'mail':
				if($this->cfg['spam_email'] > 0)
					return $this->cfg['spam_email'];
				break;
			
			case 'linkadd':
				if($this->cfg['spam_linkadd'] > 0)
					return $this->cfg['spam_linkadd'];
				break;
			
			case 'linkre':
				if($this->cfg['spam_linkview'] > 0)
					return $this->cfg['spam_linkview'];
				break;
			
			case 'search':
				if($this->cfg['spam_search'] > 0)
					return $this->cfg['spam_search'];
				break;
			
			case 'adl':
				return 3600;
		}
		
		return 0;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>