<?php
/**
 * Contains the unread-dao-class
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
 * The DAO-class for the unread-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Unread extends FWS_Singleton
{
	/**
	 * @return BS_DAO_Unread the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns all unread posts that are affected of the given post-ids. You'll get an array of:
	 * <code>array('user_id' => ..., 'post_id' => ...,'user_group' => ...)</code>
	 *
	 * @param array $ids the post-ids
	 * @return array the rows
	 */
	public function get_posts_for_posts_move($ids)
	{
		$db = FWS_Props::get()->db();
		
		if(!is_array($ids) || count($ids) == 0)
			FWS_Helper::def_error('array>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT u.user_id,u.post_id,pr.user_group FROM '.BS_TB_UNREAD.' u
			 LEFT JOIN '.BS_TB_PROFILES.' pr ON u.user_id = pr.id
			 WHERE u.post_id IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns all unread posts that are affected of the given topic-ids. You'll get an array of:
	 * <code>array('user_id' => ..., 'post_id' => ...,'user_group' => ...)</code>
	 *
	 * @param array $ids the topic-ids
	 * @return array the rows
	 */
	public function get_posts_for_topic_move($ids)
	{
		$db = FWS_Props::get()->db();
		
		if(!is_array($ids) || count($ids) == 0)
			FWS_Helper::def_error('array>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT u.user_id,u.post_id,pr.user_group FROM '.BS_TB_UNREAD.' u
			 LEFT JOIN '.BS_TB_POSTS.' p ON u.post_id = p.id
			 LEFT JOIN '.BS_TB_PROFILES.' pr ON u.user_id = pr.id
			 WHERE p.threadid IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns all unread rows with given ids for the given type
	 *
	 * @param string $type the type: rubrikid or threadid
	 * @param array $ids the ids for the type
	 * @return array all rows
	 */
	public function get_all_by_type($type,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!in_array($type,array('rubrikid','threadid')))
			FWS_Helper::def_error('inarray','type',array('rubrikid','threadid'),$type);
		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->get_rows(
			'SELECT u.* FROM '.BS_TB_UNREAD.' u
			 LEFT JOIN '.BS_TB_POSTS.' p ON u.post_id = p.id
			 WHERE p.'.$type.' IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns all unread rows for the given user
	 *
	 * @param int $id the user-id
	 * @return array all rows
	 */
	public function get_all_of_user($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->get_rows(
			'SELECT u.*,p.rubrikid,p.threadid FROM '.BS_TB_UNREAD.' u
			 LEFT JOIN '.BS_TB_POSTS.' p ON u.post_id = p.id
			 WHERE u.user_id = '.$id
		);
	}
	
	/**
	 * Create new entries for the given user
	 *
	 * @param int $userid the user-id
	 * @param array $postids an array with the post-ids to create:
	 * 	<code>array(<postID> => <isNews>,...)</code>
	 */
	public function create($userid,$postids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($userid) || $userid <= 0)
			FWS_Helper::def_error('intgt0','userid',$userid);
		if(!is_array($postids) || count($postids) == 0)
			FWS_Helper::def_error('array>0','postids',$postids);
		
		$sql = 'INSERT INTO '.BS_TB_UNREAD.' (post_id,user_id,is_news) VALUES ';
		$i = 0;
		$len = count($postids);
		foreach($postids as $pid => $is_news)
		{
			$sql .= '('.$pid.','.$userid.','.($is_news ? '1' : '0').')';
			if($i++ < $len - 1)
				$sql .= ',';
		}
		
		try
		{
			$db->execute($sql);
		}
		catch(FWS_DB_Exception_QueryFailed $e)
		{
			// ignore errors here because it doesn't really hurt if the unread post is already present
		}
	}
	
	/**
	 * Updates the post-id for the given post-id
	 *
	 * @param int $post_id the current post-id
	 * @param int $new_post_id the new post-id
	 * @return int the number of affected rows
	 */
	public function update_by_post($post_id,$new_post_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($post_id) || $post_id <= 0)
			FWS_Helper::def_error('intgt0','post_id',$post_id);
		if(!FWS_Helper::is_integer($new_post_id) || $new_post_id <= 0)
			FWS_Helper::def_error('intgt0','new_post_id',$new_post_id);
		
		return $db->update(BS_TB_UNREAD,'WHERE post_id = '.$post_id,array(
			'post_id' => $new_post_id
		));
	}
	
	/**
	 * Deletes all rows for the given user
	 *
	 * @param int $id the used-id
	 * @return int the number of affected rows
	 */
	public function delete_by_user($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		$db->execute(
			'DELETE FROM '.BS_TB_UNREAD.' WHERE user_id = '.$id
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all rows with the given posts
	 *
	 * @param array $post_ids the post-ids
	 * @return int the number of affected rows
	 */
	public function delete_posts($post_ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($post_ids) || count($post_ids) == 0)
			FWS_Helper::def_error('intarray>0','post_ids',$post_ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_UNREAD.' WHERE post_id IN ('.implode(',',$post_ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all posts with given ids for the given user
	 *
	 * @param int $id the used-id
	 * @param array $post_ids the post-ids
	 * @return int the number of affected rows
	 */
	public function delete_posts_of_user($id,$post_ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Array_Utils::is_integer($post_ids) || count($post_ids) == 0)
			FWS_Helper::def_error('intarray>0','post_ids',$post_ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_UNREAD.'
			 WHERE user_id = '.$id.' AND post_id IN ('.implode(',',$post_ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all news for the given user
	 *
	 * @param int $id the used-id
	 * @return int the number of affected rows
	 */
	public function delete_news_of_user($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		$db->execute(
			'DELETE FROM '.BS_TB_UNREAD.'
			 WHERE user_id = '.$id.' AND is_news = 1'
		);
		return $db->get_affected_rows();
	}
}
?>