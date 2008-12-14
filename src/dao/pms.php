<?php
/**
 * Contains the PMs-dao-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the PMs-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_PMs extends FWS_Singleton
{
	/**
	 * @return BS_DAO_PMs the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the total number of PMs
	 */
	public function get_count()
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(BS_TB_PMS,'id','');
	}
	
	/**
	 * Returns the number of PMs in the given folder of the given user
	 *
	 * @param string $folder the folder: inbox or outbox
	 * @param int $user_id the user-id
	 * @return int the number of PMs
	 */
	public function get_count_in_folder($folder,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$user_field = $folder == 'inbox' ? 'receiver_id' : 'sender_id';
		return $db->get_row_count(
			BS_TB_PMS,'id',' WHERE '.$user_field.' = '.$user_id.' AND pm_type = "'.$folder.'"'
		);
	}
	
	/**
	 * Returns the number of unread PMs of the given user
	 *
	 * @param int $user_id the user-id
	 * @return int the number of unread PMs
	 */
	public function get_unread_pms_count($user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->get_row_count(
			BS_TB_PMS,'id',' WHERE receiver_id = '.$user_id." AND pm_type = 'inbox' AND pm_read = 0"
		);
	}
	
	/**
	 * Returns the data of the PM with given id
	 *
	 * @param int $id the PM-id
	 * @return array the PM-data or false if not found
	 */
	public function get_by_id($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		$row = $db->get_row(
			'SELECT * FROM '.BS_TB_PMS.' WHERE id = '.$id
		);
		if(!$row)
			return false;
		
		return $row;
	}
	
	/**
	 * Returns the data of the PM with given id of the given user by detail. That means you get
	 * all fields of the PM-table and the name, group and avatar of sender and receiver.
	 *
	 * @param int $id the PM-id
	 * @param int $userid the user-id
	 * @return array the PM-details
	 */
	public function get_pm_details($id,$userid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Helper::is_integer($userid) || $userid <= 0)
			FWS_Helper::def_error('intgt0','userid',$userid);
		
		$row = $db->get_row(
			'SELECT p.*,u.`'.BS_EXPORT_USER_NAME.'` sender_name,
					 		u2.`'.BS_EXPORT_USER_NAME.'` receiver_name,a.av_pfad sender_avatar,
							a.user sender_av_owner,a2.av_pfad receiver_avatar,
							a2.user receiver_av_owner,pr.user_group sender_user_group,
							pr2.user_group receiver_user_group
			 FROM '.BS_TB_PMS.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.sender_id = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' pr ON p.sender_id = pr.id
			 LEFT JOIN '.BS_TB_AVATARS.' a ON pr.avatar = a.id
			 LEFT JOIN '.BS_TB_USER.' u2 ON p.receiver_id = u2.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' pr2 ON p.receiver_id = pr2.id
			 LEFT JOIN '.BS_TB_AVATARS.' a2 ON pr2.avatar = a2.id
			 WHERE ((p.receiver_id = '.$userid.' AND pm_type = "inbox") OR
						 (p.sender_id = '.$userid.' AND pm_type = "outbox")) AND
						 p.id = '.$id
		);
		if(!$row)
			return false;
		
		return $row;
	}
	
	/**
	 * Returns a list with all PMs. You can specify the sort and the range.
	 *
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all found PMs
	 */
	public function get_list($sort = 'id',$order = 'ASC',$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT * FROM '.BS_TB_PMS.'
			 ORDER BY '.$sort.' '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns the data of the PM with given id in the given folder and of the given user.
	 *
	 * @param int $id the PM-id
	 * @param string $folder the folder: inbox or outbox
	 * @param int $user_id the user-id
	 * @return array the PM-data or false if not found
	 */
	public function get_pm_in_folder($id,$folder,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$user_field = $folder == 'inbox' ? 'receiver_id' : 'sender_id';
		$other_user_field = $folder == 'inbox' ? 'sender_id' : 'receiver_id';
		$row = $db->get_row(
			'SELECT p.*,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_PMS.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.'.$other_user_field.' = u.`'.BS_EXPORT_USER_ID.'`
			 WHERE p.id = '.$id.' AND p.'.$user_field.' = '.$user_id.' AND
						 p.pm_type = "'.$folder.'"'
		);
		if(!$row)
			return false;
		
		return $row;
	}
	
	/**
	 * Returns all PMs in the given folder and of the given user.
	 *
	 * @param string $folder the folder: inbox or outbox
	 * @param int $user_id the user-id
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all found PMs
	 */
	public function get_pms_in_folder($folder,$user_id,$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		$uid_field = $folder == 'inbox' ? 'receiver_id' : 'sender_id';
		$other_uid_field = $folder == 'inbox' ? 'sender_id' : 'receiver_id';
		return $db->get_rows(
			'SELECT p.*,u.`'.BS_EXPORT_USER_NAME.'` user_name,pr.user_group
			 FROM '.BS_TB_PMS.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.'.$other_uid_field.' = u.`'.BS_EXPORT_USER_ID."`
			 LEFT JOIN ".BS_TB_PROFILES.' pr ON p.'.$other_uid_field.' = pr.id
			 WHERE p.'.$uid_field.' = '.$user_id.' AND p.pm_type = "'.$folder.'"
			 ORDER BY p.id DESC
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Returns the PMs with given ids that have been received or sent by the given user.
	 * You can specify the sort and the range.
	 *
	 * @param int $userid the user-id
	 * @param array $ids an array with the PM-ids
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all found PMs
	 */
	public function get_pms_of_user_by_ids($userid,$ids,$sort = 'id',$order = 'ASC',$start = 0,
		$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($userid) || $userid <= 0)
			FWS_Helper::def_error('intgt0','userid',$userid);
		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT p.*,u.`'.BS_EXPORT_USER_NAME.'` user_name,pr.user_group
			 FROM '.BS_TB_PMS.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.sender_id = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' pr ON p.sender_id = pr.id
			 WHERE ((p.receiver_id = '.$userid.' AND pm_type = "inbox") OR
						 (p.sender_id = '.$userid.' AND pm_type = "outbox"))
			 			 AND p.id IN ('.implode(',',$ids).')
			 ORDER BY '.$sort.' '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Searches for PMs by the given WHERE-clause
	 *
	 * @param int $user_id the user-id
	 * @param string $where the WHERE-clause
	 * @return array the ids of the found PMs
	 */
	public function get_pm_ids_by_search($user_id,$where)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$rows = $db->get_rows(
			'SELECT p.id
			 FROM '.BS_TB_PMS.' p
			 LEFT JOIN '.BS_TB_USER.' u1 ON p.receiver_id = u1.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_USER.' u2 ON p.sender_id = u2.`'.BS_EXPORT_USER_ID.'`
			 '.$where.'
			 AND ((p.receiver_id = '.$user_id.' AND pm_type = "inbox") OR
					 (p.sender_id = '.$user_id.' AND pm_type = "outbox"))'
		);
		$ids = array();
		foreach($rows as $row)
			$ids[] = $row['id'];
		return $ids;
	}
	
	/**
	 * Returns the last PMs with <var>$uid2</var> that belong to <var>$uid1</var>.
	 * You'll get all fields of the PM-table and the sender-name and his/her group.
	 *
	 * @param int $uid1 the owner of the PMs
	 * @param int $uid2 the conversation partner
	 * @param int $number the max. number of rows (0 = unlimited)
	 * @return array the found PMs
	 */
	public function get_last_pms_with_user($uid1,$uid2,$number = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($uid1) || $uid1 <= 0)
			FWS_Helper::def_error('intgt0','uid1',$uid1);
		if(!FWS_Helper::is_integer($uid2) || $uid2 <= 0)
			FWS_Helper::def_error('intgt0','uid2',$uid2);
		if(!FWS_Helper::is_integer($number) || $number < 0)
			FWS_Helper::def_error('intge0','number',$number);
		
		return $db->get_rows(
			'SELECT p.*,u.`'.BS_EXPORT_USER_NAME.'` sender_name,pr.user_group
			 FROM '.BS_TB_PMS.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.sender_id = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' pr ON p.sender_id = pr.id
			 WHERE (p.receiver_id = '.$uid2.' AND p.sender_id = '.$uid1.' AND
							p.pm_type = "outbox") OR
						 (p.sender_id = '.$uid2.' AND p.receiver_id = '.$uid1.' AND
							p.pm_type = "inbox")
			 ORDER BY p.id DESC
			 '.($number > 0 ? 'LIMIT '.$number : '')
		);
	}
	
	/**
	 * Returns the id of the previous PM in the given folder of the given user
	 *
	 * @param int $pm_id the PM-id where to start
	 * @param int $user_id the user-id
	 * @param string $folder the folder: inbox or outbox
	 * @return int the PM-id or false if not found
	 */
	public function get_prev_pm_id_of_user($pm_id,$user_id,$folder = 'inbox')
	{
		return $this->get_np_pm_id_of_user($pm_id,$user_id,$folder,'<');
	}
	
	/**
	 * Returns the id of the next PM in the given folder of the given user
	 *
	 * @param int $pm_id the PM-id where to start
	 * @param int $user_id the user-id
	 * @param string $folder the folder: inbox or outbox
	 * @return int the PM-id or false if not found
	 */
	public function get_next_pm_id_of_user($pm_id,$user_id,$folder = 'inbox')
	{
		return $this->get_np_pm_id_of_user($pm_id,$user_id,$folder,'>');
	}
	
	/**
	 * @return array an array with all PM-ids whose text is invalid
	 */
	public function get_invalid_pm_ids()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT id FROM '.BS_TB_PMS.' WHERE pm_text = "" AND pm_text_posted != ""'
		);
	}
	
	/**
	 * Creates a new PM with the given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_PMS,$fields);
	}
	
	/**
	 * Sets the number of attachment for the given PM-id
	 *
	 * @param int $id the PM-id
	 * @param int $count the number of attachments
	 * @return int the number of affected rows
	 */
	public function set_attachment_count($id,$count)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->update(BS_TB_PMS,'WHERE id = '.$id,array('attachment_count' => $count));
	}
	
	/**
	 * Sets wether the given PMs are read or unread
	 *
	 * @param array $ids an array with the PM-ids
	 * @param int $user_id the user-id
	 * @param int $flag 0 = unread, 1 = read
	 * @return int the number of affected rows
	 */
	public function set_read_flag($ids,$user_id,$flag)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->update(
			BS_TB_PMS,
			'WHERE id IN ('.implode(',',$ids).') AND receiver_id = '.$user_id.' AND pm_type = "inbox"',
			array('pm_read' => $flag ? 1 : 0)
		);
	}
	
	/**
	 * Updates the text of the given PM
	 *
	 * @param int $id the PM-id
	 * @param string $text the text of the PM
	 * @param string $text_posted the posted text of the PM
	 * @return int the number of affected rows
	 */
	public function update_text($id,$text,$text_posted)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_PMS,'WHERE id = '.$id,array(
			'pm_text' => $text,
			'pm_text_posted' => $text_posted
		));
	}
	
	/**
	 * Deletes all PMs with given ids of the given user
	 *
	 * @param array $ids an array with the PM-ids
	 * @param int $user_id the user-id
	 * @return int the number of affected rows
	 */
	public function delete_pms_of_user($ids,$user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$db->execute(
			'DELETE FROM '.BS_TB_PMS.' WHERE id IN ('.implode(',',$ids).') AND
			 ((receiver_id = '.$user_id.' AND pm_type = "inbox") OR
			  (sender_id = '.$user_id.' AND pm_type = "outbox"))'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all PMs of the given users
	 *
	 * @param array $ids an array with the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_user_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_PMS.'
			 WHERE (receiver_id IN ('.implode(',',$ids).') AND pm_type = "inbox") OR
						 (sender_id IN ('.implode(',',$ids).') AND pm_type = "outbox")'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Determines the next of previous PM in the given folder of the given user
	 *
	 * @param int $pm_id the PM-id where to start
	 * @param int $user_id the user-id
	 * @param string $folder the folder: inbox or outbox
	 * @param string $op the operation: &lt; or &gt;
	 * @return int the id or false if not found
	 */
	protected function get_np_pm_id_of_user($pm_id,$user_id,$folder = 'inbox',$op = '>')
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($pm_id) || $pm_id <= 0)
			FWS_Helper::def_error('intgt0','pm_id',$pm_id);
		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		if($folder == 'inbox')
			$where = 'receiver_id = '.$user_id.' AND pm_type = "inbox"';
		else
			$where = 'sender_id = '.$user_id.' AND pm_type = "outbox"';
		
		$row = $db->get_row(
			'SELECT id FROM '.BS_TB_PMS.'
			 WHERE id '.$op.' '.$pm_id.' AND '.$where.'
			 ORDER BY id '.($op == '>' ? 'ASC' : 'DESC')
		);
		if(!$row)
			return false;
		
		return $row['id'];
	}
}
?>