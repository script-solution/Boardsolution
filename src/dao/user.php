<?php
/**
 * Contains the user-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the user-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * If you want to get just the fields from the user-table please use this class. If you need
 * some information from the profile-table, too, use {@link BS_DAO_Profile}.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_User extends BS_DAO_UserBase
{
	/**
	 * @return BS_DAO_User the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Determines the number of existing users
	 *
	 * @param int $active wether the user has to be activated: -1 = indifferent, 0 = no, 1 = yes
	 * @param int $banned wether the user has to be banned: -1 = indifferent, 0 = no, 1 = yes
	 * @return int the number of users
	 */
	public function get_user_count($active = -1,$banned = -1)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(
			BS_TB_PROFILES.' p','p.id',$this->get_activenbanned($active,$banned)
		);
	}
	
	/**
	 * Checks wether the given user-id exists
	 *
	 * @param int $user_id the user-id
	 * @return boolean true if so
	 */
	public function id_exists($user_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		return $db->get_row_count(BS_TB_PROFILES,'id',' WHERE id = '.$user_id) > 0;
	}
	
	/**
	 * Checks wether the given username exists
	 *
	 * @param string $user_name the name
	 * @param int $user_id the user-id to which the name must not belong (0 = don't use)
	 * @return boolean true if so
	 */
	public function name_exists($user_name,$user_id = 0)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(
			BS_TB_USER,
			'id',
			' WHERE `'.BS_EXPORT_USER_NAME."` = '".$user_name."' AND
							`".BS_EXPORT_USER_ID."` != ".$user_id
		) > 0;
	}
	
	/**
	 * Checks wether the given email exists
	 *
	 * @param string $user_email the email-address
	 * @param int $user_id the user-id to which the email must not belong (0 = don't use)
	 * @return boolean true if so
	 */
	public function email_exists($user_email,$user_id = 0)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(
			BS_TB_USER,
			'id',
			' WHERE `'.BS_EXPORT_USER_EMAIL."` = '".$user_email."' AND
							`".BS_EXPORT_USER_ID."` != ".$user_id
		) > 0;
	}
	
	/**
	 * Returns the user with the given id
	 *
	 * @param int $id the user-id
	 * @param int $active wether the user has to be activated: -1 = indifferent, 0 = no, 1 = yes
	 * @param int $banned wether the user has to be banned: -1 = indifferent, 0 = no, 1 = yes
	 * @return array the user-data as associative array or false if not found
	 */
	public function get_user_by_id($id,$active = 1,$banned = 0)
	{
		$rows = $this->get_users_by_ids(array($id),$active,$banned);
		if(count($rows) == 0)
			return false;
		
		return $rows[0];
	}
	
	/**
	 * Returns all users with the given ids
	 *
	 * @param array $ids the user-ids
	 * @param int $active wether the user has to be activated: -1 = indifferent, 0 = no, 1 = yes
	 * @param int $banned wether the user has to be banned: -1 = indifferent, 0 = no, 1 = yes
	 * @return array an array with all found users
	 */
	public function get_users_by_ids($ids,$active = 1,$banned = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids))
			FWS_Helper::def_error('intarray','ids',$ids);
		
		// if no ids given the query is useless
		if(count($ids) == 0)
			return array();
		
		$where = $this->get_activenbanned($active,$banned);
		return $db->get_rows(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_USER.' u
			 LEFT JOIN '.BS_TB_PROFILES.' p ON u.`'.BS_EXPORT_USER_ID.'` = p.id
			 '.$where.' AND p.id IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns the user with the given name
	 *
	 * @param string $name the name of the user (case-sensitive and complete!)
	 * @return array the user-data or false if not found
	 */
	public function get_user_by_name($name)
	{
		$rows = $this->get_users_by_names(array($name));
		if(count($rows) == 0)
			return false;
		
		return $rows[0];
	}
	
	/**
	 * Returns all users with the given names
	 *
	 * @param array $names an array with all user-names (case-sensitive and complete!)
	 * @return array all found users
	 */
	public function get_users_by_names($names)
	{
		return $this->get_users_by_names_impl($this->get_fields(),$names);
	}
	
	/**
	 * Returns the user with the given email
	 *
	 * @param string $email the email-address of the user
	 * @return array the user-data
	 */
	public function get_user_by_email($email)
	{
		return $this->get_user_by_email_impl($this->get_fields(),$email);
	}
	
	/**
	 * Returns all users whose name is like the given name.
	 * They will be sorted by experience descending.
	 *
	 * @param string $name the name (may be a part)
	 * @param int $max the maximum number of users to find
	 * @return array the found users
	 */
	public function get_users_like_name($name,$max = 6)
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_USER.' u
			 LEFT JOIN '.BS_TB_PROFILES.' p ON u.`'.BS_EXPORT_USER_ID.'` = p.id
			 WHERE p.active = 1 AND p.banned = 0 AND u.`'.BS_EXPORT_USER_NAME.'` LIKE "%'.$name.'%"
			 ORDER BY p.exppoints DESC
			 '.($max > 0 ? 'LIMIT '.$max : '')
		);
	}
	
	/**
	 * Determines the number of users that are member of at least one of the given groups. You may
	 * specify an empty groups-array and you may also specify user-ids that are required.
	 *
	 * @param array $group_ids an array of group-ids
	 * @param array $user_ids an array of user-ids
	 * @return int the number of users
	 */
	public function get_users_by_groups_count($group_ids,$user_ids = array())
	{
		$db = FWS_Props::get()->db();

		$where = $this->get_user_by_groups_where($group_ids,$user_ids);
		return $db->get_row_count(BS_TB_PROFILES.' p','*',$where);
	}
	
	/**
	 * Returns all users that are member of at least one of the given groups. You may
	 * specify an empty groups-array and you may also specify user-ids that are required.
	 * Additionally you can define a start-position and the max. number of user
	 *
	 * @param array $group_ids an array of group-ids
	 * @param array $user_ids an array of user-ids
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of elements (for the LIMIT-statement). 0 = all
	 * @return array all found users
	 */
	public function get_users_by_groups($group_ids,$user_ids = array(),$start = 0,$count = 0)
	{
		return $this->get_users_by_groups_impl($this->get_fields(),$group_ids,$user_ids,$start,$count);
	}
	
	/**
	 * Searches for the given fields and returns the number of found users.
	 *
	 * @param string $user_name the name of the user (or a part of it)
	 * @param string $user_email the email of the user (or a part of it)
	 * @param int $register_date the min. timestamp of the registration
	 * @param array $user_groups an array with group-ids
	 * @return int the number of found users
	 */
	public function get_search_user_count($user_name = '',$user_email = '',$register_date = 0,
		$user_groups = array())
	{
		$db = FWS_Props::get()->db();

		$where = $this->get_search_where_clause($user_name,$user_email,$register_date,$user_groups);
		return $db->get_row_count(
			BS_TB_PROFILES.' p',
			'p.id',
			' LEFT JOIN '.BS_TB_USER.' u ON p.id = u.`'.BS_EXPORT_USER_ID.'` '.$where
		);
	}
	
	/**
	 * Performs a custom search and returns the number of found users
	 *
	 * @param string $where the WHERE-clause
	 * @return int the number of found users
	 */
	public function get_custom_search_user_count($where)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(
			BS_TB_PROFILES.' p',
			'p.id',
			' LEFT JOIN '.BS_TB_USER.' AS u ON p.id = u.`'.BS_EXPORT_USER_ID.'` '.$where
		);
	}
	
	/**
	 * Creates an entry in the user-table with the given values
	 *
	 * @param string $user_name the user-name (has to be unique!)
	 * @param string $user_email the email
	 * @param string $user_pw the password in plain-text
	 * @param int $user_id the id to use (null = automatically)
	 * @return int the id of the user that has been used
	 */
	public function create($user_name,$user_email,$user_pw,$user_id = null)
	{
		$db = FWS_Props::get()->db();

		if(empty($user_name))
			FWS_Helper::def_error('notempty','user_name',$user_name);
		if(empty($user_email))
			FWS_Helper::def_error('notempty','user_email',$user_email);
		if(empty($user_pw))
			FWS_Helper::def_error('notempty','user_pw',$user_pw);
		if($user_id !== null && (!FWS_Helper::is_integer($user_id) || $user_id <= 0))
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		
		$fields = array(
			BS_EXPORT_USER_NAME => $user_name,
			BS_EXPORT_USER_EMAIL => $user_email,
			BS_EXPORT_USER_PW => md5($user_pw)
		);
		if($user_id !== null)
			$fields[BS_EXPORT_USER_ID] = $user_id;
		
		return $db->insert(BS_TB_USER,$fields);
	}
	
	/**
	 * Updates fields for the given user-id
	 *
	 * @param int $id the user-id
	 * @param string $user_name the user-name (has to be unique!) (empty = ignore)
	 * @param string $user_email the email (empty = ignore)
	 * @param string $user_pw the password-hash (empty = ignore)
	 * @return int the number of affected rows
	 */
	public function update($id,$user_name = '',$user_pw = '',$user_email = '')
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		// nothing to do?
		if(empty($user_name) && empty($user_pw) && empty($user_email))
			return 0;
		
		// build fields
		$fields = array();
		if(!empty($user_name))
			$fields[BS_EXPORT_USER_NAME] = $user_name;
		if(!empty($user_email))
			$fields[BS_EXPORT_USER_EMAIL] = $user_email;
		if(!empty($user_pw))
			$fields[BS_EXPORT_USER_PW] = $user_pw;
		
		return $db->update(BS_TB_USER,'WHERE `'.BS_EXPORT_USER_ID.'` = '.$id,$fields);
	}
	
	/**
	 * Deletes the users with given ids
	 *
	 * @param array $ids an array with all ids to delete
	 * @return int the number of affected rows
	 */
	public function delete($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_USER.' WHERE `'.BS_EXPORT_USER_ID.'` IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * @return string the fields for a "not-full" user
	 */
	protected function get_fields()
	{
		return 'u.`'.BS_EXPORT_USER_ID.'` id,u.`'.BS_EXPORT_USER_NAME.'` user_name,'
			.'u.`'.BS_EXPORT_USER_EMAIL.'` user_email,u.`'.BS_EXPORT_USER_PW.'` user_pw';
	}
}
?>