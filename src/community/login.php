<?php
/**
 * Contains the community-login-interface
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The interface for login- and logout-events
 *
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 */
interface BS_Community_Login
{
	/**
	 * Will be called as soon as a user has logged in
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function user_login($user);
	
	/**
	 * Will be called as soon as a user has logged out
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function user_logout($user);
}
?>