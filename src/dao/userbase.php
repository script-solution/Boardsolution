<?php
/**
 * Contains the dao-user-base class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-class for the user- and profile-DAO. It provides some helper-methods for both
 * classes.
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_DAO_UserBase extends PLIB_Singleton
{
	/**
	 * Builds the WHERE-clause for the search-methods
	 *
	 * @param string $user_name the name of the user (or a part of it)
	 * @param string $user_email the email of the user (or a part of it)
	 * @param int $register_date the min. timestamp of the registration
	 * @param array $user_groups an array with group-ids
	 * @return string the WHERE-clause
	 */
	protected function _get_search_where_clause($user_name = '',$user_email = '',$register_date = 0,
		$user_groups = array())
	{
		$user_name = str_replace('*','%',(string)$user_name);
		$user_email = str_replace('*','%',(string)$user_email);
		if(!PLIB_Helper::is_integer($register_date) || $register_date < 0)
			PLIB_Helper::def_error('intge0','register_date',$register_date);
		if(!PLIB_Array_Utils::is_integer($user_groups))
			PLIB_Helper::def_error('intarray','user_groups',$user_groups);
		
		$where = ' WHERE p.active = 1 AND p.banned = 0';
		if($user_name != null)
			$where .= ' AND u.`'.BS_EXPORT_USER_NAME."` LIKE '%".$user_name."%'";
		if($user_email != null)
			$where .= ' AND u.`'.BS_EXPORT_USER_EMAIL."` LIKE '%".$user_email."%'";
		if($register_date != 0)
			$where .= ' AND registerdate >= '.$register_date;
		if(count($user_groups) > 0)
		{
			$where .= ' AND (';
			foreach($user_groups as $id)
				$where .= 'FIND_IN_SET('.$id.',p.user_group) OR ';
			$where = PLIB_String::substr($where,0,PLIB_String::strlen($where) - 4).')';
		}
		
		return $where;
	}
	
	/**
	 * Returns the given fields for the given groups and ids
	 *
	 * @param string $fields the fields to select
	 * @param array $group_ids an array of group-ids
	 * @param array $user_ids an array of user-ids
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of elements (for the LIMIT-statement). 0 = all
	 * @return array all found users
	 */
	protected function _get_users_by_groups($fields,$group_ids,$user_ids,$start = 0,$count = 0)
	{
		$where = $this->_get_user_by_groups_where($group_ids,$user_ids);
		$limit = $this->_get_limit($start,$count);
		return $this->db->sql_rows(
			'SELECT '.$fields.'
			 FROM '.BS_TB_USER.' u
			 LEFT JOIN '.BS_TB_PROFILES.' p ON u.`'.BS_EXPORT_USER_ID.'` = p.id
			 '.$where.'
			 '.$limit
		);
	}
	
	/**
	 * Builds the WHERE-clause for the get_[full_]users_by_groups methods.
	 *
	 * @param array $group_ids an array of group-ids
	 * @param array $user_ids an array of user-ids
	 * @return string the WHERE-clause
	 */
	protected function _get_user_by_groups_where($group_ids,$user_ids)
	{
		if(!PLIB_Array_Utils::is_integer($group_ids))
			PLIB_Helper::def_error('intarray','group_ids',$group_ids);
		if(!PLIB_Array_Utils::is_integer($user_ids))
			PLIB_Helper::def_error('intarray','user_ids',$user_ids);
		
		$where = '';
		if(count($group_ids) > 0 || count($user_ids) > 0)
		{
			$where = ' WHERE p.banned = 0 AND p.active = 1 AND ( ';
			if(count($group_ids) > 0)
			{
				foreach($group_ids as $gid)
					$where .= 'FIND_IN_SET('.$gid.',p.user_group) > 0 OR ';
			}
			if(count($user_ids) > 0)
				$where .= ' p.id IN ('.implode(',',$user_ids).') OR ';
			
			$where = PLIB_String::substr($where,0,PLIB_String::strlen($where) - 4).')';
		}
		
		return $where;
	}
	
	/**
	 * Returns the user with the given email. You can specify if you want to get all (=full)
	 * or just the fields from the user-table
	 *
	 * @param string $fields the fields to select
	 * @param string $email the email-address
	 * @return array the user-data
	 */
	protected function _get_user_by_email($fields,$email)
	{
		return $this->db->sql_fetch(
			'SELECT '.$fields.'
			 FROM '.BS_TB_USER.' u
			 LEFT JOIN '.BS_TB_PROFILES.' p ON u.`'.BS_EXPORT_USER_ID.'` = p.id
			 WHERE p.active = 1 AND p.banned = 0 AND u.`'.BS_EXPORT_USER_EMAIL.'` = "'.$email.'"'
		);
	}

	/**
	 * Returns all users by the given names. You can specify if you want to get all (=full)
	 * or just the fields from the user-table
	 *
	 * @param string $fields the fields to select
	 * @param array $names an array with all user-names (case-sensitive and complete!)
	 * @return array all found users
	 */
	protected function _get_users_by_names($fields,$names)
	{
		if(!is_array($names))
			PLIB_Helper::def_error('array','names',$names);
		
		// if no ids given the query is useless
		if(count($names) == 0)
			return array();
		
		return $this->db->sql_rows(
			'SELECT '.$fields.'
			 FROM '.BS_TB_USER.' u
			 LEFT JOIN '.BS_TB_PROFILES.' p ON u.`'.BS_EXPORT_USER_ID.'` = p.id
			 WHERE p.active = 1 AND p.banned = 0 AND
			 	`'.BS_EXPORT_USER_NAME.'` IN ("'.implode('","',$names).'")'
		);
	}
	
	/**
	 * Generates the WHERE-statement for the fields "active" and "banned".
	 *
	 * @param int $active wether the user has to be activated: -1 = indifferent, 0 = no, 1 = yes
	 * @param int $banned wether the user has to be banned: -1 = indifferent, 0 = no, 1 = yes
	 * @return string the WHERE-clause (contains at least "WHERE 1")
	 */
	protected function _get_activenbanned($active,$banned)
	{
		if(!PLIB_Helper::is_integer($active) || !in_array($active,array(-1,0,1)))
			PLIB_Helper::def_error('numbetween','active',-1,1,$active);
		if(!PLIB_Helper::is_integer($banned) || !in_array($banned,array(-1,0,1)))
			PLIB_Helper::def_error('numbetween','banned',-1,1,$banned);
		
		$where = ' WHERE 1';
		if($active >= 0)
			$where .= ' AND p.active = '.$active;
		if($banned >= 0)
			$where .= ' AND p.banned = '.$banned;
		return $where;
	}
	
	/**
	 * Checks the given arguments and returns the ORDER BY-statement
	 *
	 * @param string $sort the field to sort by. The profile-table is called "p" and the user-table "u".
	 * 	You may use all fields of them.
	 * @param string $order the order of the elements: ASC or DESC
	 * @return string the ORDER BY statement
	 */
	protected function _get_sort($sort,$order)
	{
		if(!in_array($order,array('ASC','DESC')))
			PLIB_Helper::def_error('in_array','order',array('ASC','DESC'),$order);
		
		return 'ORDER BY '.$sort.' '.$order;
	}
	
	/**
	 * Checks the given arguments and returns the LIMIT statement that should be used
	 *
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the number of elements (for the LIMIT-statement). 0 = all
	 * @return string the LIMIT-statement
	 */
	protected function _get_limit($start,$count)
	{
		if(!PLIB_Helper::is_integer($start) || $start < 0)
			PLIB_Helper::def_error('intge0','start',$start);
		if(!PLIB_Helper::is_integer($count) || $count < 0)
			PLIB_Helper::def_error('intge0','count',$count);
		
		if($count > 0)
			return 'LIMIT '.$start.','.$count;
		return '';
	}
}
?>