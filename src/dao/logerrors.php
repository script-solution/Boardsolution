<?php
/**
 * Contains the log-errors-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the log-errors-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_LogErrors extends FWS_Singleton
{
	/**
	 * @return BS_DAO_LogErrors the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return int the total number of errors
	 */
	public function get_count()
	{
		$db = FWS_Props::get()->db();

		return $db->sql_num(BS_TB_LOG_ERRORS,'id','');
	}
	
	/**
	 * The number of logs which match (query, message or backtrace) the given keyword
	 *
	 * @param string $keyword the keyword to search for
	 * @return int the number of found errors
	 */
	public function get_count_by_keyword($keyword)
	{
		$db = FWS_Props::get()->db();

		$where = $this->get_keyword_where($keyword);
		return $db->sql_num(BS_TB_LOG_ERRORS.' l','l.id',$where);
	}
	
	/**
	 * Returns all logs with given ids
	 *
	 * @param array $ids the ids
	 * @return array the logs
	 */
	public function get_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		return $db->sql_rows(
			'SELECT * FROM '.BS_TB_LOG_ERRORS.'
			 WHERE id IN ('.implode(',',$ids).')'
		);
	}
	
	/**
	 * Returns all logs. You can specify the sort and range.
	 *
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all found logs
	 */
	public function get_list($sort = 'l.id',$order = 'ASC',$start = 0,$count = 0)
	{
		return $this->get_list_by_keyword('',$sort,$order,$start,$count);
	}
	
	/**
	 * Returns all logs which match (query, message or backtrace) the given keyword. You can specify
	 * the sort and range.
	 *
	 * @param string $keyword the keyword to search for
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @param int $start the start-position (for the LIMIT-statement)
	 * @param int $count the max. number of rows (for the LIMIT-statement) (0 = unlimited)
	 * @return array all found logs
	 */
	public function get_list_by_keyword($keyword,$sort = 'id',$order = 'ASC',$start = 0,$count = 0)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($start) || $start < 0)
			FWS_Helper::def_error('intge0','start',$start);
		if(!FWS_Helper::is_integer($count) || $count < 0)
			FWS_Helper::def_error('intge0','count',$count);
		
		$where = $keyword ? $this->get_keyword_where($keyword) : '';
		return $db->sql_rows(
			'SELECT l.*,u.`'.BS_EXPORT_USER_NAME.'` user_name
			 FROM '.BS_TB_LOG_ERRORS.' l
			 LEFT JOIN '.BS_TB_USER.' u ON u.`'.BS_EXPORT_USER_ID.'` = l.user_id
			 '.$where.'
			 ORDER BY '.$sort.' '.$order.'
			 '.($count > 0 ? 'LIMIT '.$start.','.$count : '')
		);
	}
	
	/**
	 * Creates a new log-entry with the given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$db = FWS_Props::get()->db();

		$db->sql_insert(BS_TB_LOG_ERRORS,$fields,false);
		return $db->get_last_insert_id();
	}
	
	/**
	 * Clears all logs
	 */
	public function clear()
	{
		$db = FWS_Props::get()->db();

		$db->sql_qry('TRUNCATE TABLE '.BS_TB_LOG_ERRORS);
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
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_LOG_ERRORS.' WHERE date < '.(time() - $timeout)
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes the logs with given ids
	 *
	 * @param array $ids all ids to delete
	 * @return int the number of affected rows
	 */
	public function delete($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->sql_qry(
			'DELETE FROM '.BS_TB_LOG_ERRORS.' WHERE id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Builds the WHERE-clause for the given keyword
	 *
	 * @param string $keyword the keyword to search for
	 * @return string the WHERE-clause
	 */
	protected function get_keyword_where($keyword)
	{
		$where = ' WHERE l.message LIKE "%'.$keyword.'%" OR l.backtrace LIKE "%'.$keyword.'%" OR';
		$where .= ' l.query LIKE "%'.$keyword.'%"';
		return $where;
	}
}
?>