<?php
/**
 * Contains the log-ips-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the log-ips-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_LogIPs extends FWS_Singleton
{
	/**
	 * @return BS_DAO_LogErrors the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns the number of found entries for the given WHERE-clause
	 *
	 * @param string $where the WHERE-clause
	 * @return int the number of rows
	 */
	public function get_count_by_search($where)
	{
		$db = FWS_Props::get()->db();

		return $db->get_row_count(
			BS_TB_LOG_IPS.' l','l.id',
			' LEFT JOIN '.BS_TB_USER.' u ON l.user_id = u.`'.BS_EXPORT_USER_ID.'`
			'.$where
		);
	}
	
	/**
	 * Returns the entry for the given action and ip. Optional you can specify a timeout.
	 *
	 * @param string $action the action
	 * @param string $ip the ip
	 * @param int $timeout the timeout in seconds (0 = ignore)
	 * @return array the entry or false if not found
	 */
	public function get_by_action_for_ip($action,$ip,$timeout = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($timeout) || $timeout < 0)
			FWS_Helper::def_error('intge0','timeout',$timeout);
		
		$data = $db->get_row(
			'SELECT * FROM '.BS_TB_LOG_IPS.'
			 WHERE action = "'.$action.'" AND user_ip = "'.$ip.'"
			 '.($timeout > 0 ? 'AND date > '.(time() - $timeout) : '')
		);
		if(!$data)
			return false;
		return $data;
	}
	
	/**
	 * Returns all rows for the given WHERE-clause. You can specify the sort and range.
	 *
	 * @param string $where the WHERE-clause
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all found logs
	 */
	public function get_list_by_search($where,$sort = 'l.id',$order = 'ASC',$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		return $db->get_rows(
			'SELECT l.*,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_LOG_IPS.' l
			 LEFT JOIN '.BS_TB_USER.' u ON l.user_id = u.`'.BS_EXPORT_USER_ID.'`
			 '.$where.'
			 ORDER BY '.$sort.' '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Creates a new entry for the current user and the given action
	 *
	 * @param string $action the action
	 * @return int the used id
	 */
	public function create($action)
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();

		return $db->insert(BS_TB_LOG_IPS,array(
			'user_ip' => $user->get_user_ip(),
			'user_id' => $user->get_user_id(),
			'user_agent' => $user->get_user_agent(),
			'date' => time(),
			'action' => $action
		));
	}
	
	/**
	 * Deletes all logs
	 */
	public function clear()
	{
		$db = FWS_Props::get()->db();

		$db->execute('TRUNCATE TABLE '.BS_TB_LOG_IPS);
	}
	
	/**
	 * Deletes the logs with given ids
	 *
	 * @param array $ids the ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_LOG_IPS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all logs that are older than <code>time() - $timeout</code>.
	 *
	 * @param int $timeout the timeout in seconds
	 * @return int the number of affected rows
	 */
	public function delete_timedout($timeout)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($timeout) || $timeout <= 0)
			FWS_Helper::def_error('intgt0','timeout',$timeout);
		
		$db->execute(
			'DELETE FROM '.BS_TB_LOG_ERRORS.' WHERE date < '.(time() - $timeout)
		);
		return $db->get_affected_rows();
	}
}
?>