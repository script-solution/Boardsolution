<?php
/**
 * Contains the unsent-posts-dao-class
 * 
 * @package			Boardsolution
 * @subpackage	src.dao
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
 * The DAO-class for the unsent-posts-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_UnsentPosts extends FWS_Singleton
{
	/**
	 * @return BS_DAO_UnsentPosts the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns a list with all post-ids and the associated user that should be notified.
	 * You'll get the user-id, post-id, user-email, user-name, wether the post should
	 * be included and the user-language.
	 *
	 * @return array the found rows
	 */
	public function get_notification_list()
	{
		$db = FWS_Props::get()->db();

		$time = time();
		return $db->get_rows(
			'SELECT p.id,up.post_id,u.`'.BS_EXPORT_USER_EMAIL.'` user_email,
							u.`'.BS_EXPORT_USER_NAME.'` user_name,p.emails_include_post,p.forum_lang
			 FROM '.BS_TB_PROFILES.' p
			 LEFT JOIN '.BS_TB_USER.' u ON u.`'.BS_EXPORT_USER_ID.'`= p.id
			 RIGHT JOIN '.BS_TB_UNSENT_POSTS.' up ON p.id = up.user_id
			 WHERE p.active = 1 AND p.banned = 0 AND
						((email_notification_type = "1day" AND
							last_email_notification < '.($time - 86400).') OR
						 (email_notification_type = "2days" AND
							last_email_notification < '.($time - 86400 * 2).') OR
						 (email_notification_type = "1week" AND
							last_email_notification < '.($time - 86400 * 7).'))'
		);
	}
	
	/**
	 * Creates a new entry for the given users and post
	 *
	 * @param int $post_id the post-id
	 * @param array $user_ids all users
	 */
	public function create($post_id,$user_ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($post_id) || $post_id <= 0)
			FWS_Helper::def_error('intgt0','post_id',$post_id);
		if(!FWS_Array_Utils::is_integer($user_ids) || count($user_ids) == 0)
			FWS_Helper::def_error('intarray>0','user_ids',$user_ids);
		
		$sql = 'INSERT INTO '.BS_TB_UNSENT_POSTS.' (post_id,user_id) VALUES ';
		foreach($user_ids as $uid)
			$sql .= '('.$post_id.','.$uid.'),';
		$sql = FWS_String::substr($sql,0,-1);
		$db->execute($sql);
	}
	
	/**
	 * Deletes all entries for the given users
	 *
	 * @param array $ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_UNSENT_POSTS.' WHERE user_id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>