<?php
/**
 * Contains the events-module
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
		$month = FWS_Date::get_formated_date('m');
		$day = FWS_Date::get_formated_date('d');
		foreach(BS_DAO::get_profile()->get_birthday_users((int)$month,(int)$day) as $data)
			$this->birthdays[] = $data;
	}
}
?>