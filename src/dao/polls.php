<?php
/**
 * Contains the polls-dao-class
 *
 * @version			$Id: polls.php 796 2008-05-29 18:23:27Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the polls-table. Contains all methods to manipulate the table-content and
 * retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_Polls extends PLIB_Singleton
{
	/**
	 * @return BS_DAO_Polls the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns the data of the poll by the given topic-id. That means you'll get the following fields:
	 * <code>
	 * 	array(
	 * 		'type' => <pollID>,
	 * 		'multichoice' => ...,
	 * 		'thread_closed' => ...
	 * 	)
	 * </code>
	 *
	 * @param int $tid the topic-id
	 * @return array the data
	 */
	public function get_data_by_topic_id($tid)
	{
		if(!PLIB_Helper::is_integer($tid) || $tid <= 0)
			PLIB_Helper::def_error('intgt0','tid',$tid);
		
		$row = $this->db->sql_fetch(
			'SELECT type,multichoice,thread_closed FROM '.BS_TB_THREADS.' t
			 LEFT JOIN '.BS_TB_POLL.' p ON t.type = p.pid
			 WHERE t.id = '.$tid
		);
		if(!$row)
			return false;
		
		return $row;
	}
	
	/**
	 * Returns all options for the given poll-id
	 *
	 * @param int $id the poll-id
	 * @param string $sort the sort-column
	 * @param string $order the order: ASC or DESC
	 * @return array all options
	 */
	public function get_options_by_id($id,$sort = 'id',$order = 'ASC')
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		return $this->db->sql_rows(
			'SELECT * FROM '.BS_TB_POLL.'
			 WHERE pid = '.$id.'
			 ORDER BY '.$sort.' '.$order
		);
	}
	
	/**
	 * @return int the next id for a poll
	 */
	public function get_next_id()
	{
		$res = $this->db->sql_fetch('SELECT MAX(pid) FROM '.BS_TB_POLL);
		return $res[0] + 1;
	}
	
	/**
	 * Creates a new poll-option
	 *
	 * @param int $id the poll-id
	 * @param string $option the option-name
	 * @param int $multichoice wether multichoice is enabled
	 * @return int the used id
	 */
	public function create($id,$option,$multichoice)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_insert(BS_TB_POLL,array(
			'pid' => $id,
			'option_name' => $option,
			'multichoice' => $multichoice ? 1 : 0
		));
		return $this->db->get_last_insert_id();
	}
	
	/**
	 * Votes for the given option-id (!)
	 *
	 * @param int $id the option-id
	 * @return the number of affected rows
	 */
	public function vote($id)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_update(BS_TB_POLL,'WHERE id = '.$id,array(
			'option_value' => array('option_value + 1')
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Sets wether multichoice is enabled for the given poll
	 *
	 * @param int $id the poll-id
	 * @param int $multichoice wether multichoice is enabled
	 * @return the number of affected rows
	 */
	public function set_multichoice($id,$multichoice)
	{
		if(!PLIB_Helper::is_integer($id) || $id <= 0)
			PLIB_Helper::def_error('intgt0','id',$id);
		
		$this->db->sql_update(BS_TB_POLL,'WHERE pid = '.$id,array(
			'multichoice' => $multichoice ? 1 : 0
		));
		return $this->db->get_affected_rows();
	}
	
	/**
	 * Deletes all polls with given ids
	 *
	 * @param array $ids the poll-ids
	 * @return the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			PLIB_Helper::def_error('intarray>0','ids',$ids);
		
		$this->db->sql_qry(
			'DELETE FROM '.BS_TB_POLL.' WHERE pid IN ('.implode(',',$ids).')'
		);
		return $this->db->get_affected_rows();
	}
}
?>