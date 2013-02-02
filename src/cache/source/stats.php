<?php
/**
 * Contains the simple-db-implementation for the source
 * 
 * @package			Boardsolution
 * @subpackage	src.cache
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
			'posts_last' => $posts_last,
			'logins_total' => $logins,
			'max_online' => 0,
			'logins_today' => 0,
			'logins_yesterday' => 0,
			'logins_last' => $lastlogin,
			'last_edit' => $last_edit
		));
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>