<?php
/**
 * Contains the profile-dao-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the profile-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * If you want to get all fields from the profile-table and user-table please use this class.
 * If you need just the fields from the user-table use {@link BS_DAO_User}.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Profile extends BS_DAO_UserBase
{
	/**
	 * @return BS_DAO_Profile the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}

	/**
	 * Returns a list with the found users.
	 * You may specify the sort of the elements, which extract you want to get and wether the users
	 * should be active and/or banned.
	 *
	 * @param string $sort the field to sort by. The profile-table is called "p" and the user-table "u".
	 * 	You may use all fields of them.
	 * @param string $order the order of the elements: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of elements (for the LIMIT-statement). 0 = all
	 * @param int $active wether the user has to be activated: -1 = indifferent, 0 = no, 1 = yes
	 * @param int $banned wether the user has to be banned: -1 = indifferent, 0 = no, 1 = yes
	 * @return array all found users
	 */
	public function get_users($sort = 'p.id',$order = 'ASC',$start = 0,$count = 0,
		$active = 1,$banned = 0)
	{
		$db = FWS_Props::get()->db();

		$where = $this->get_activenbanned($active,$banned);
		$sort = $this->get_sort($sort,$order);
		$limit = $this->get_limit($start,$count);
		return $db->get_rows(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_PROFILES.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.id = u.`'.BS_EXPORT_USER_ID.'`
			 '.$where.'
			 '.$sort.'
			 '.$limit
		);
	}
	
	/**
	 * Returns the user with the given id
	 *
	 * @param int $id the user id
	 * @param int $active wether the user has to be activated: -1 = indifferent, 0 = no, 1 = yes
	 * @param int $banned wether the user has to be banned: -1 = indifferent, 0 = no, 1 = yes
	 * @return array the user-data or false if not found
	 */
	public function get_user_by_id($id,$active = 1,$banned = 0)
	{
		$rows = $this->get_users_by_ids(array($id),'p.id','ASC',0,1,$active,$banned);
		if(count($rows) == 0)
			return false;
		
		return $rows[0];
	}
	
	/**
	 * Returns all users with the given ids. You can also specify the sort of the result
	 *
	 * @param array $ids the user-ids
	 * @param string $sort the field to sort by. The profile-table is called "p" and the user-table "u".
	 * 	You may use all fields of them.
	 * @param string $order the order of the elements: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of elements (for the LIMIT-statement). 0 = all
	 * @param int $active wether the user has to be activated: -1 = indifferent, 0 = no, 1 = yes
	 * @param int $banned wether the user has to be banned: -1 = indifferent, 0 = no, 1 = yes
	 * @return array an array with all found users
	 */
	public function get_users_by_ids($ids,$sort = 'p.id',$order = 'ASC',$start = 0,$count = 0,
		$active = 1,$banned = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		// if no ids given the query is useless
		if(count($ids) == 0)
			return array();
		
		$sort = $this->get_sort($sort,$order);
		return $db->get_rows(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_USER.' u
			 LEFT JOIN '.BS_TB_PROFILES.' p ON u.`'.BS_EXPORT_USER_ID.'` = p.id
			 '.$this->get_activenbanned($active,$banned).' AND p.id IN ('.implode(',',$ids).')
			 '.$sort.'
			 '.$this->get_limit($start,$count)
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
	 * Searches for the given fields and returns the found users. You can also specify
	 * the sort of the users and the start and count for the LIMIT-statement.
	 *
	 * @param string $user_name the name of the user (or a part of it)
	 * @param string $user_email the email of the user (or a part of it)
	 * @param int $register_date the min. timestamp of the registration
	 * @param array $user_groups an array with group-ids
	 * @param string $sort the field to sort by. The profile-table is called "p" and the user-table "u".
	 * 	You may use all fields of them.
	 * @param string $order the order of the elements: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of elements (for the LIMIT-statement). 0 = all
	 * @return array all found users
	 */
	public function get_users_by_search($user_name = '',$user_email = '',$register_date = 0,
		$user_groups = array(),$sort = 'p.id',$order = 'ASC',$start = 0,$count = 0)
	{
		$where = $this->get_search_where_clause($user_name,$user_email,$register_date,$user_groups);
		return $this->get_users_by_custom_search($where,$sort,$order,$start,$count);
	}
	
	/**
	 * Performs a custom search and returns all found users. You can also specify
	 * the sort of the users and the start and count for the LIMIT-statement.
	 *
	 * @param string $where the WHERE-clause
	 * @param string $sort the field to sort by. The profile-table is called "p" and the user-table "u".
	 * 	You may use all fields of them.
	 * @param string $order the order of the elements: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of elements (for the LIMIT-statement). 0 = all
	 * @return array all found users
	 */
	public function get_users_by_custom_search($where,$sort = 'p.id',$order = 'ASC',
		$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_USER.' u
			 LEFT JOIN '.BS_TB_PROFILES.' p ON u.`'.BS_EXPORT_USER_ID.'` = p.id
			 '.$where.'
			 '.$this->get_sort($sort,$order).'
			 '.$this->get_limit($start,$count)
		);
	}
	
	/**
	 * Returns all users that have delayed email-notification enabled. The given used-id
	 * is excluded from the result
	 *
	 * @param int $user_id the user-id to exclude
	 * @return array all found users
	 */
	public function get_users_with_delayed_notify($user_id)
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_USER.' u
			 LEFT JOIN '.BS_TB_PROFILES.' p ON u.`'.BS_EXPORT_USER_ID.'` = p.id
			 WHERE email_notification_type != "immediatly" AND p.id != '.$user_id
		);
	}
	
	/**
	 * Returns the newest user
	 *
	 * @return array the user-data
	 */
	public function get_newest_user()
	{
		$db = FWS_Props::get()->db();

		return $db->get_row(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_PROFILES.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.id = u.`'.BS_EXPORT_USER_ID.'`
			 WHERE p.active = 1 AND p.banned = 0
			 ORDER BY p.registerdate DESC'
		);
	}
	
	/**
	 * Returns the latest active user
	 *
	 * @return array the user-data
	 */
	public function get_last_active_user()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$db = FWS_Props::get()->db();

		$ghost_mode = ($user->is_admin() || $cfg['allow_ghost_mode'] == 0 ? '1' : '0');
		return $db->get_row(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_PROFILES.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.id = u.`'.BS_EXPORT_USER_ID.'`
			 WHERE p.id != '.$user->get_user_id().' AND p.active = 1 AND
						 p.banned = 0 AND (p.ghost_mode = 0 OR '.$ghost_mode.')
			 ORDER BY p.lastlogin DESC'
		);
	}
	
	/**
	 * Returns all users that have birthday in the given months
	 *
	 * @param array $months an array of months
	 * @return array all found users
	 */
	public function get_birthday_users_in_months($months)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($months) || count($months) == 0)
			FWS_Helper::def_error('intarray>0','months',$months);
		
		$where = 'WHERE p.active = 1 AND p.banned = 0 AND p.add_birthday != "0000-00-00" AND (';
		foreach($months as $month)
			$where .= 'SUBSTRING(p.add_birthday,6,2) = "'.sprintf('%02d',$month).'" OR ';
		$where = FWS_String::substr($where,0,-4).')';
		
		return $db->get_rows(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_PROFILES.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.id = u.`'.BS_EXPORT_USER_ID.'`
			 '.$where
		);
	}
	
	/**
	 * Returns all users that have birthday in the given month and optional on the given day.
	 * You can also specify a max. number of users you want to get.
	 * 
	 * @param int $month the month (1..12)
	 * @param int $day the day (1..31) or 0 for ignore
	 * @param int $number the max. number of users
	 * @return array all found users
	 */
	public function get_birthday_users($month,$day = 0,$number = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($month) || $month < 1 || $month > 12)
			FWS_Helper::def_error('numbetween','month',1,12,$month);
		if(!FWS_Helper::is_integer($day) || $day < 0 || $day > 31)
			FWS_Helper::def_error('numbetween','day',0,31,$day);
		if(!FWS_Helper::is_integer($number) || $number < 0)
			FWS_Helper::def_error('intge0','number',$number);
		
		$month = sprintf('%02d',$month);
		$day = sprintf('%02d',$day);
		$where = 'WHERE p.add_birthday != \'0000-00-00\' AND p.active = 1 AND p.banned = 0';
		$where .= ' AND SUBSTRING(p.add_birthday,6,2) = '.$month;
		if($day != 0)
			$where .= ' AND SUBSTRING(p.add_birthday,9,2) = '.$day;
		
		return $db->get_rows(
			'SELECT '.$this->get_fields().'
			 FROM '.BS_TB_PROFILES.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.id = u.`'.BS_EXPORT_USER_ID.'`
			 '.$where.'
			 '.($number > 0 ? 'LIMIT '.$number : '')
		);
	}
	
	/**
	 * @return int the total number of logins of all users
	 */
	public function get_total_login_count()
	{
		$db = FWS_Props::get()->db();

		$res = $db->get_row(
			'SELECT SUM(logins) AS total FROM '.BS_TB_PROFILES.'
			 WHERE active = 1 AND banned = 0'
		);
		return $res['total'];
	}
	
	/**
	 * @return int the timestamp of the last login (in the profile-table)
	 */
	public function get_lastlogin()
	{
		$db = FWS_Props::get()->db();

		$res = $db->get_row(
			'SELECT lastlogin FROM '.BS_TB_PROFILES.'
			 WHERE active = 1 AND banned = 0
			 ORDER BY lastlogin DESC'
		);
		return $res['lastlogin'];
	}
	
	/**
	 * @return array an array with all profile-ids whose signatures are invalid
	 */
	public function get_invalid_signature_ids()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT id FROM '.BS_TB_PROFILES.' WHERE signature_posted != "" AND signatur = ""'
		);
	}
	
	/**
	 * The query for the statistics to grab users sorted by posts-per-day.
	 * You get the fields:
	 * <code>
	 * 	array(
	 * 		'id',
	 * 		'user_name',
	 * 		'registerdate',
	 * 		'per_day',
	 * 		'user_group'
	 *	)
	 * </code>
	 *
	 * @param int $number the max. number of users
	 * @return array all found users
	 */
	public function get_users_stats_postsperday($number)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($number) || $number <= 0)
			FWS_Helper::def_error('intgt0','number',$number);
		
		return $db->get_rows(
			'SELECT p.id,p.posts,u.`'.BS_EXPORT_USER_NAME.'` user_name,p.registerdate,
			 				p.posts / (('.time().' - p.registerdate) / 86400) per_day,p.user_group
			 FROM '.BS_TB_PROFILES.' p
			 LEFT JOIN '.BS_TB_USER.' u ON p.id = u.`'.BS_EXPORT_USER_ID.'`
			 WHERE p.registerdate < '.(time() - 86400).' AND p.active = 1 AND p.banned = 0
			 ORDER BY per_day DESC LIMIT '.$number
		);
	}
	
	/**
	 * The query for the statistics to get users grouped by the register-date.
	 * You get the fields:
	 * <code>
	 * 	array(
	 * 		'registerdate',
	 * 		'num' // the number of users
	 * 		'date', // the date as YYYYMM
	 *	)
	 * </code>
	 *
	 * @return unknown
	 */
	public function get_users_stats_grouped_by_regdate()
	{
		$db = FWS_Props::get()->db();

		return $db->get_rows(
			'SELECT registerdate,COUNT(id) num,
							CONCAT(YEAR(FROM_UNIXTIME(registerdate)),MONTH(FROM_UNIXTIME(registerdate))) date
			 FROM '.BS_TB_PROFILES.'
			 GROUP BY date
			 ORDER BY registerdate DESC'
		);
	}
	
	/**
	 * Updates the given fields for all users
	 * 
	 * @param array $fields the fields to update. See {@link FWS_DB_Connection::update} for details
	 * @return int the number of affected rows
	 */
	public function update_all($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->update(BS_TB_PROFILES,'',$fields);
	}
	
	/**
	 * Updates the given fields for the user with the given id
	 * 
	 * @param array $fields the fields to update. See {@link FWS_DB_Connection::update} for details
	 * @param int $id the user-id
	 * @return int the number of affected rows
	 */
	public function update_user_by_id($fields,$id)
	{
		return $this->update_users_by_ids($fields,array($id));
	}
	
	/**
	 * Updates the given fields for all users with the given ids
	 * 
	 * @param array $fields the fields to update. See {@link FWS_DB_Connection::update} for details
	 * @param array $ids an array with all user-ids
	 * @return int the number of affected rows
	 */
	public function update_users_by_ids($fields,$ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->update(BS_TB_PROFILES,' WHERE id IN ('.implode(',',$ids).')',$fields);
	}
	
	/**
	 * Applies the default-template to all users are currently using a theme with one of the given
	 * ids.
	 *
	 * @param array $theme_ids the theme-ids
	 * @return int the number of affected rows
	 */
	public function update_theme_to_default($theme_ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($theme_ids) || count($theme_ids) == 0)
			FWS_Helper::def_error('intarray>0','theme_ids',$theme_ids);
		
		$fields = array('forum_style' => 0);
		return $db->update(
			BS_TB_PROFILES,'WHERE forum_style IN ('.implode(',',$theme_ids).')',$fields
		);
	}
	
	/**
	 * Adds an additional field with the given name, type and length to the profile-table.
	 * The method assumes that the field doesn't already exist (the name)
	 *
	 * @param string $name the name of the field (without "add_")
	 * @param string $type the type: int, text, date, enum, line
	 * @param int $length the length of the field (just for int and line)
	 */
	public function add_additional_fields($name,$type,$length = 0)
	{
		$db = FWS_Props::get()->db();

		if(empty($name))
			FWS_Helper::def_error('notempty','name',$name);
		
		$db->execute(
			'ALTER TABLE '.BS_TB_PROFILES.'
			 ADD `add_'.$name.'`
			 '.$this->get_field_sql_syntax($type,$length)
		);
	}
	
	/**
	 * Changes the type or length of the additional field with given name
	 *
	 * @param string $name the name of the field (without "add_")
	 * @param string $type the type: int, text, date, enum, line
	 * @param int $length the length of the field (just for int and line)
	 */
	public function change_additional_field($name,$type,$length)
	{
		$db = FWS_Props::get()->db();

		if(empty($name))
			FWS_Helper::def_error('notempty','name',$name);
		
		$db->execute(
			'ALTER TABLE '.BS_TB_PROFILES.'
			 CHANGE `add_'.$name.'` `add_'.$name.'`
			 '.$this->get_field_sql_syntax($type,$length)
		);
	}
	
	/**
	 * Deletes the additional field with given name

	 * @param string $name the name of the field (without "add_")
	 */
	public function delete_additional_fields($name)
	{
		$db = FWS_Props::get()->db();

		if(empty($name))
			FWS_Helper::def_error('notempty','name',$name);
		
		$db->execute(
			'ALTER TABLE '.BS_TB_PROFILES.'
			 DROP `add_'.$name.'`'
		);
	}
	
	/**
	 * Creates an entry in the profile-table
	 *
	 * @param int $id the id to use
	 * @param array $groups an array with all groups the user should belong to
	 * @return int the used id
	 */
	public function create($id,$groups)
	{
		$cfg = FWS_Props::get()->cfg();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		if(!FWS_Array_Utils::is_integer($groups) || count($groups) == 0)
			FWS_Helper::def_error('intarray>0','groups',$groups);
		
		$fields = array(
			'id' => $id,
			'banned' => 0,
			'active' => 1,
			'allow_pms' => 1,
			'registerdate' => time(),
			// TODO keep this?
			'lastlogin' => time(),
			'timezone' => $cfg['default_timezone'],
			'last_unread_update' => time(),
			'bbcode_mode' => $cfg['msgs_default_bbcode_mode'],
			'posts_order' => $cfg['default_posts_order'],
			'user_group' => implode(',',$groups).','
		);
		return $this->create_custom($fields);
	}
	
	/**
	 * Creates a profile-entry with custom fields
	 *
	 * @param array $fields all fields to set
	 * @return int the used id
	 */
	public function create_custom($fields)
	{
		$db = FWS_Props::get()->db();

		return $db->insert(BS_TB_PROFILES,$fields);
	}
	
	/**
	 * Deletes all profiles with given ids
	 *
	 * @param array $ids all ids to delete
	 * @return int the number of affected rows
	 */
	public function delete($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM  '.BS_TB_PROFILES.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}

	/**
	 * generates the string to change a field in the database corresponding to the field-type
	 *
	 * @param string $type the field-type
	 * @param int $length the field-length
	 * @return string the SQL-syntax
	 */
	protected function get_field_sql_syntax($type,$length = 0)
	{
		if(!FWS_Helper::is_integer($length))
			FWS_Helper::def_error('integer','length',$length);
		
		switch((string)$type)
		{
			case 'int':
				return 'INT('.$length.') NULL';

			case 'text':
				return 'TEXT NOT NULL DEFAULT \'\'';
			
			case 'date':
				return 'DATE NOT NULL';

			case 'enum':
				return "INT(10) NOT NULL DEFAULT '-1'";

			default:
				return 'VARCHAR('.$length.') NOT NULL DEFAULT \'\'';
		}
	}
	
	/**
	 * @return string the fields for a "full" user
	 */
	protected function get_fields()
	{
		return 'u.*,u.`'.BS_EXPORT_USER_ID.'` id,u.`'.BS_EXPORT_USER_NAME.'` user_name,'
			.'u.`'.BS_EXPORT_USER_EMAIL.'` user_email,p.*';
	}
}
?>