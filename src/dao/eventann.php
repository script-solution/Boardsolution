<?php
/**
 * Contains the events-announcements-dao-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The DAO-class for the event-announcements-table. Contains all methods to manipulate the
 * table-content and retrieve rows from it.
 * <br>
 * Please make sure that you escape all data that you pass to this class because it uses it
 * as it is (By default the input-class does escape the data).
 *
 * @package			Boardsolution
 * @subpackage	src.dao
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_DAO_EventAnn extends FWS_Singleton
{
	/**
	 * @return BS_DAO_EventAnn the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Returns the number of announcements for the given event
	 *
	 * @param int $event_id the event-id
	 * @return int the number of announcements
	 */
	public function get_count_of_event($event_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($event_id) || $event_id <= 0)
			FWS_Helper::def_error('intgt0','event_id',$event_id);
		
		return $db->get_row_count(BS_TB_EVENT_ANN,'*','WHERE event_id = '.$event_id);
	}
	
	/**
	 * Checks wether the given user is announced for the given event
	 *
	 * @param int $user_id the user-id
	 * @param int $event_id the event-id
	 * @return boolean wether the user is announced
	 */
	public function is_announced($user_id,$event_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($event_id) || $event_id <= 0)
			FWS_Helper::def_error('intgt0','event_id',$event_id);
		
		return $db->get_row_count(
			BS_TB_EVENT_ANN,'*','WHERE user_id = '.$user_id.' AND event_id = '.$event_id
		) > 0;
	}
	
	/**
	 * Returns all announced users for the given event
	 *
	 * @param int $event_id the event-id
	 * @return array an array with the user
	 */
	public function get_user_of_event($event_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($event_id) || $event_id <= 0)
			FWS_Helper::def_error('intgt0','event_id',$event_id);
		
		return $db->get_rows(
			'SELECT e.user_id,u.`'.BS_EXPORT_USER_NAME.'` user_name,p.user_group
			 FROM '.BS_TB_EVENT_ANN.' e
			 LEFT JOIN '.BS_TB_USER.' u ON e.user_id = u.`'.BS_EXPORT_USER_ID.'`
			 LEFT JOIN '.BS_TB_PROFILES.' p ON e.user_id = p.id
			 WHERE e.event_id = '.$event_id
		);
	}
	
	/**
	 * Announces the given user for the given event
	 *
	 * @param int $user_id the user-id
	 * @param int $event_id the event-id
	 */
	public function announce($user_id,$event_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($event_id) || $event_id <= 0)
			FWS_Helper::def_error('intgt0','event_id',$event_id);
		
		$db->insert(BS_TB_EVENT_ANN,array(
			'user_id' => $user_id,
			'event_id' => $event_id
		));
	}
	
	/**
	 * Unannounces the given user from the given event
	 *
	 * @param int $user_id the user-id
	 * @param int $event_id the event-id
	 * @return int the number of affected rows
	 */
	public function leave($user_id,$event_id)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Helper::is_integer($user_id) || $user_id <= 0)
			FWS_Helper::def_error('intgt0','user_id',$user_id);
		if(!FWS_Helper::is_integer($event_id) || $event_id <= 0)
			FWS_Helper::def_error('intgt0','event_id',$event_id);
		
		$db->execute(
			'DELETE FROM '.BS_TB_EVENT_ANN.' WHERE user_id = '.$user_id.' AND event_id = '.$event_id
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries for the given events
	 *
	 * @param array $ids the event-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_events($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_EVENT_ANN.' WHERE event_id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
	
	/**
	 * Deletes all entries of the given users
	 *
	 * @param array $ids the user-ids
	 * @return int the number of affected rows
	 */
	public function delete_by_users($ids)
	{
		$db = FWS_Props::get()->db();

		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			FWS_Helper::def_error('intarray>0','ids',$ids);
		
		$db->execute(
			'DELETE FROM '.BS_TB_EVENT_ANN.' WHERE user_id IN ('.implode(',',$ids).')'
		);
		return $db->get_affected_rows();
	}
}
?>