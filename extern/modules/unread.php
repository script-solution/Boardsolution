<?php
/**
 * Contains the unread-module
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
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
 * Contains the unread topics and forums for the current user
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_API_Module_unread extends BS_API_Module
{
	/**
	 * a numeric array with the unread forum-ids
	 *
	 * @var array
	 */
	public $unread_forums = array();
	
	/**
	 * a numeric array with the unread topics of the following form:
	 * <code>
	 * array(
	 * 	'id' => <topicID>,
	 * 	'forum_id' => <forumID>,
	 * 	'post_id' => <idOfTheFirstUnreadPost>
	 * )
	 * </code>
	 *
	 * @var array
	 */
	public $unread_topics = array();
	
	/**
	 * the number of unread pms
	 *
	 * @var integer
	 */
	public $unread_pms = 0;
	
	public function run()
	{
		$user = FWS_Props::get()->user();
		$unread = FWS_Props::get()->unread();

		if($user->is_loggedin())
		{
			$this->unread_pms = $user->get_profile_val('unread_pms');
			$this->unread_forums = $unread->get_unread_forums();
			
			$topics = $unread->get_unread_topics();
			if(is_array($topics))
			{
				foreach($topics as $tid => $udata)
				{
					$this->unread_topics[] = array(
						'id' => $tid,
						'forum_id' => $udata[1],
						'post_id' => $udata[0]
					);
				}
			}
		}
	}
}
?>