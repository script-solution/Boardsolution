<?php
/**
 * Contains the polls-dao-class
 *
 * @version			$Id$
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
class BS_DAO_Polls extends FWS_Singleton
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
	 * @return array|bool the data or false if failed
	 */
	public function get_data_by_topic_id($tid)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($tid) || $tid <= 0)
			FWS_Helper::def_error('intgt0','tid',$tid);
		
		$row = $db->get_row(
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
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->get_rows(
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
		$db = FWS_Props::get()->db();

		$res = $db->get_row('SELECT MAX(pid) AS pid FROM '.BS_TB_POLL);
		return $res['pid'] + 1;
	}
	
	/**
	 * Creates a new poll-option
	 *
	 * @param int $id the poll-id
	 * @param string $option the option-name
	 * @param bool $multichoice wether multichoice is enabled
	 * @return int the used id
	 */
	public function create($id,$option,$multichoice)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->insert(BS_TB_POLL,array(
			'pid' => $id,
			'option_name' => $option,
			'multichoice' => $multichoice ? 1 : 0
		));
	}
	
	/**
	 * Votes for the given option-id (!)
	 *
	 * @param int $id the option-id
	 * @return int the number of affected rows
	 */
	public function vote($id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_POLL,'WHERE id = '.$id,array(
			'option_value' => array('option_value + 1')
		));
	}
	
	/**
	 * Sets wether multichoice is enabled for the given poll
	 *
	 * @param int $id the poll-id
	 * @param int $multichoice wether multichoice is enabled
	 * @return int the number of affected rows
	 */
	public function set_multichoice($id,$multichoice)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
		return $db->update(BS_TB_POLL,'WHERE pid = '.$id,array(
			'multichoice' => $multichoice ? 1 : 0
		));
	}
	
	/**
	 * Deletes all polls with given ids
	 *
	 * @param array $ids the poll-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_ids($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_POLL.' WHERE pid IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>