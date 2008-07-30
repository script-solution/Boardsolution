<?php
/**
 * Contains the simple-db-implementation for the source
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.cache
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An implementation of the source for the statistics.
 *
 * @package			Boardsolution
 * @subpackage	src.cache
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Cache_Source_Stats extends FWS_Object implements FWS_Cache_Source
{
	public function get_content()
	{
		$logins = BS_DAO::get_profile()->get_total_login_count();
		$lastlogin = BS_DAO::get_profile()->get_lastlogin();
		$posts_last = BS_DAO::get_posts()->get_lastpost_time();
		$last_edit = BS_DAO::get_posts()->get_lastedit_time();
		
		return array(array(
			'posts_last' => $posts_last['post_time'],
			'logins_total' => $logins,
			'max_online' => 0,
			'logins_today' => 0,
			'logins_yesterday' => 0,
			'logins_last' => $lastlogin,
			'last_edit' => $last_edit['edited_date']
		));
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>