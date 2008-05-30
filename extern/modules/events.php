<?php
/**
 * Contains the events-module
 * 
 * @version			$Id: events.php 735 2008-05-23 07:49:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The events-module which grabs all events and birthdays from BS so that you can use them.
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_API_Module_events extends BS_API_Module
{
	/**
	 * the events of the next x days
	 * an array of the form:
	 * <code>
	 * array(
	 * 	'id' => <eventID>,
	 * 	'title' => <eventTitle>,
	 * 	'topic_id' => <topicID>,
	 * 	'forum_id' => <forumID>,
	 * 	'begin' => <eventBegin>,
	 * 	'end' => <eventEnd>
	 * );
	 * </code>
	 *
	 * @var array
	 */
	public $events = array();

	/**
	 * the birthdays today
	 * an array of the form:
	 * <code>
	 * array(
	 * 	'id' => <userID>,
	 * 	'user_name' => <userName>,
	 * 	'add_birthday' => <date>
	 * );
	 * </code>
	 *
	 * @var array
	 */
	public $birthdays = array();

	public function run($params = array('event_timeout' => 432000))
	{
		// grab events from db
		foreach(BS_DAO::get_events()->get_next_events($params['event_timeout']) as $data)
		{
			$this->events[] = array(
				'id' => $data['id'],
				'topic_id' => $data['tid'],
				'forum_id' => $data['rubrikid'],
				'title' => $data['event_title'],
				'begin' => $data['event_begin'],
				'end' => $data['event_end']
			);
		}

		// grab birthdays from db
		$month = PLIB_Date::get_formated_date('m');
		$day = PLIB_Date::get_formated_date('d');
		foreach(BS_DAO::get_profile()->get_birthday_users($month,$day) as $data)
			$this->birthdays[] = $data;
	}
}
?>