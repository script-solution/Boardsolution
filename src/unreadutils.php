<?php
/**
 * Contains the unread-utils-class
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
 * Contains some utility-methods for the unread-topics / -posts
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_UnreadUtils extends FWS_UtilBase
{
	/**
	 * Removes all unread-information for the given forums from the unread-table.
	 * Note that this method requires that the forums exist!
	 *
	 * @param array $fids all forums that should be deleted (note that subforums are not included
	 * 	automatically!)
	 */
	public static function remove_forums($fids)
	{
		if(!is_array($fids))
			FWS_Helper::def_error('array','fids',$fids);
		
		self::_remove($fids,'rubrikid');
	}
	
	/**
	 * Removes all unread-information for the given topics from the unread-table.
	 * Note that this method requires that the topics exist!
	 *
	 * @param array $tids all topics that should be deleted
	 */
	public static function remove_topics($tids)
	{
		if(!is_array($tids))
			FWS_Helper::def_error('array','tids',$tids);
		
		self::_remove($tids,'threadid');
	}
	
	/**
	 * The method that retrieves the posts and deletes them from the unread-table.
	 *
	 * @param array $ids the ids
	 * @param string $field the name of the field that should be used for the ids in the posts-table
	 */
	private static function _remove($ids,$field)
	{
		// nothing to do?
		if(count($ids) == 0)
			return;
		
		// at first we need to know all post-ids from the topic/forum
		$post_ids = array();
		foreach(BS_DAO::get_unread()->get_all_by_type($field,$ids) as $data)
			$post_ids[] = $data['post_id'];
		
		// nothing found?
		if(count($post_ids) == 0)
			return;
		
		// ok, delete all posts
		BS_DAO::get_unread()->delete_posts($post_ids);
		
		//remove the entries for the e-mail notifications
		BS_DAO::get_unsentposts()->delete_by_posts($post_ids);
	}
	
	/**
	 * Removes all unread-information for the given posts from the unread-table
	 *
	 * @param array $pids all posts that should be deleted
	 * @param int $tid the topic-id from which the posts are
	 */
	public static function remove_posts($pids,$tid)
	{
		if(!is_array($pids))
			FWS_Helper::def_error('array','pids',$pids);
		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		// nothing to do?
		if(count($pids) == 0)
			return;
		
		// find the next post-id and replace them
		$rem = array();
		foreach($pids as $pid)
		{
			$next = BS_DAO::get_posts()->get_next_post_id($pid,$tid);
			// is there no next post?
			if($next === false)
			{
				$rem[] = $pid;
				continue;
			}
			
			// otherwise we replace the post-id
			BS_DAO::get_unread()->update_by_post($pid,$next);
		}
		
		// remove all posts that have no next post
		if(count($rem) > 0)
		{
			BS_DAO::get_unread()->delete_posts($rem);
			
			//remove the entries for the e-mail notifications
			BS_DAO::get_unsentposts()->delete_by_posts($rem);
		}
	}
}
?>