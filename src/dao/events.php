<?php
/**
 * Contains the events-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the events-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Events extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Events the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns the event-data of the given event-id
	 *
	 * @param int $id the event-id
	 * @return array the data or false if not found
	 */
	public function get_by_id($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$row = $this->db->sql_fetch('SELECT * FROM '.BS_TB_EVENTS.' WHERE id = '.$id);
		if(!$row)
			return false;
		
		return $row;
	}
	
	/**
	 * Returns the event-data of the given topic-id
	 *
	 * @param int $tid the topic-id
	 * @return array the data or false if not found
	 */
	public function get_by_topic_id($tid)
	{
		if(!PLIB_Helper::is_integer($tid) || $tid <= 0)
			PLIB_Helper::def_error('intgt0','tid',$tid);
		
		$row = $this->db->sql_fetch('SELECT * FROM '.BS_TB_EVENTS.' WHERE tid = '.$tid);
		if(!$row)
			return false;
		
		return $row;
	}
	
	/**
	 * Returns all events between the two given timestamps. You can exclude forums.
	 *
	 * @param int $min_date the minimum date as timestamp
	 * @param int $max_date the maximum date as timestamp
	 * @param array $excl_fids an array of forums to exclude
	 * @return array all found events
	 */
	public function get_events_between($min_date,$max_date,$excl_fids = array())
	{
		if(!PLIB_Helper::is_integer($min_date) || $min_date < 0)
			PLIB_Helper::def_error('intge0','min_date',$min_date);
		if(!PLIB_Helper::is_integer($max_date) || $max_date < 0)
			PLIB_Helper::def_error('intge0','max_date',$max_date);
		
		return $this->db->sql_rows(
			'SELECT e.*,t.rubrikid
			 FROM '.BS_TB_EVENTS.' e
			 LEFT JOIN '.BS_TB_THREADS.' t ON e.tid = t.id AND t.type = -1
			 WHERE event_begin >= '.$min_date.' AND event_begin <= '.$max_date.'
			 '.(count($excl_fids) > 0 ? ' AND (e.tid = 0 OR t.rubrikid NOT IN ('
					.implode(',',$excl_fids).'))' : '')
		);
	}
	
	/**
	 * Returns the next events between now and now + the given number of seconds
	 *
	 * @param int $seconds the number of seconds
	 * @param int $number the max. number of rows (0 = unlimited)
	 * @return array all found events
	 */
	public function get_next_events($seconds,$number = 0)
	{
		if(!PLIB_Helper::is_integer($seconds) || $seconds < 0)
			PLIB_Helper::def_error('intge0','seconds',$seconds);
		if(!PLIB_Helper::is_integer($number) || $number < 0)
			PLIB_Helper::def_error('intge0','number',$number);
		
		$timeout = time() + $seconds;
		return $this->db->sql_rows(
			'SELECT e.*,t.rubrikid
			 FROM '.BS_TB_EVENTS.' e
			 LEFT JOIN '.BS_TB_THREADS.' t ON e.tid = t.id
			 WHERE ((event_end = 0 AND event_begin >= '.time().') OR event_end >= '.time().') AND
						 ((event_end <= '.$timeout.' and event_end != 0) OR event_begin <= '.$timeout.')
			 ORDER BY event_begin ASC
			 '.($number > 0 ? 'LIMIT '.$number : '')
		);
	}
	
	/**
	 * Creates a new event with given fields
	 *
	 * @param array $fields the fields to set
	 * @return int the used id
	 */
	public function create($fields)
	{
		$this->db->sql_insert(BS_TB_EVENTS,$fields);
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Updates the given fields for the given event
	 *
	 * @param int $id the event-id
	 * @param array $fields the fields to set
	 * @return the number of affected rows
	 */
	public function update($id,$fields)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_update(BS_TB_EVENTS,'WHERE id = '.$id,$fields);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Updates the given fields for the given topic-id
	 *
	 * @param int $tid the topic-id
	 * @param array $fields the fields to set
	 * @return the number of affected rows
	 */
	public function update_by_topicid($tid,$fields)
	{
		if(!PLIB_Helper::is_integer($tid) || $tid <= 0)
			PLIB_Helper::def_error('intgt0','tid',$tid);
		
		$this->db->sql_update(BS_TB_EVENTS,'WHERE tid = '.$tid,$fields);
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all events with the given ids
	 *
	 * @param array $ids the event-ids
	 * @return the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		return $this->_delete_by('id',$ids);
	}
	
	/**
	 * Deletes all events with the given topic-ids
	 *
	 * @param array $tids the topic-ids
	 * @return the number of affected rows
	 */
	public function delete_by_topicids($tids)
	{
		return $this->_delete_by('tid',$tids);
	}
	
	/**
	 * Deletes entries with the given ids
	 *
	 * @param string $field the field-name
	 * @param array $ids the ids
	 * @return the number of affected rows
	 */
	protected function _delete_by($field,$ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_EVENTS.' WHERE '.$field.' IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
}
?>