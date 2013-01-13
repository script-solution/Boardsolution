<?php
/**
 * Contains the latest-topics-module
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
 * Contains the latest topics of the board.
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_API_Module_latest_topics extends BS_API_Module
{
	/**
	 * an numeric array with the latest topics in the following form:
	 * <code>
	 * array(
	 * 	'id' => <topicID>,
	 * 	'name' => <topicName>,
	 * 	'replies' => <numberOfReplies>,
	 * 	'creation_date' => <creationDate>,
	 * 	'creation_user_name' => <creationUserName>,
	 * 	'creation_user_id' => <creationUserID>,
	 * 	'lastpost_date' => <lastPostDate>,
	 * 	'lastpost_user_name' => <lastPostUserName>,
	 * 	'lastpost_user_id' => <lastPostUserID>,
	 * 	'forum_id' => <forumID>,
	 * 	'forum_name' => <forumName>
	 * )
	 * </code>
	 *
	 * @var array
	 */
	public $latest_topics = array();
	
	public function run($params = array('limit' => 10))
	{
		$cfg = FWS_Props::get()->cfg();

		// denied forums?
		$denied = array();
		if($cfg['hide_denied_forums'] == 1)
			$denied = BS_ForumUtils::get_denied_forums(false);
		
		// ensure that the params are valid
		if(!$params)
			$params = array();
		
		if(!isset($params['limit']) || !FWS_Helper::is_integer($params['limit']))
			$params['limit'] = 10;
		
		// lets grab the topics
		foreach(BS_DAO::get_topics()->get_latest_topics($params['limit'],$denied) as $data)
		{
			$this->latest_topics[] = array(
				'id' => $data['id'],
				'name' => $data['name'],
				'replies' => $data['posts'],
				'creation_date' => $data['post_time'],
				'creation_user_name' => $data['post_user'] > 0 ? $data['username'] : $data['post_an_user'],
				'creation_user_id' => $data['post_user'],
				'lastpost_id' => $data['lastpost_id'],
				'lastpost_date' => $data['lastpost_time'],
				'lastpost_user_name' => $data['lastpost_user'] > 0 ? $data['lp_username'] : $data['lastpost_an_user'],
				'lastpost_user_id' => $data['lastpost_user'],
				'forum_id' => $data['rubrikid'],
				'forum_name' => $data['rubrikname']
			);
		}
	}
}
?>