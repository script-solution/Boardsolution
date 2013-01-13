<?php
/**
 * Contains the helper class for the BS_Actions-class
 * 
 * @package			Boardsolution
 * @subpackage	src.action
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
 * Some helper-methods for the BS_Actions-class
 * 
 * @package			Boardsolution
 * @subpackage	src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Helper extends FWS_UtilBase
{
	/**
	 * Adjusts the last-post-time if necessary
	 *
	 * @param int $max_post_time the maximum post time of the posts which are deleted
	 */
	public static function adjust_last_post_time($max_post_time)
	{
		$cache = FWS_Props::get()->cache();

		$stats_data = $cache->get_cache('stats')->current();
	
		// refresh lastpost?
		if($max_post_time == $stats_data['posts_last'])
		{
			$lastpost_time = BS_DAO::get_topics()->get_last_post_time();
			$cache->get_cache('stats')->set_element_field(
				0,'posts_last',$lastpost_time
			);
			$cache->store('stats');
		}
	}
}
?>