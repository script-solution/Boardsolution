<?php
/**
 * Contains the event-utils-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Contains some methods for events and birthdays. This class is realized as singleton.
 * 
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_EventUtils extends FWS_UtilBase
{
	/**
	 * Builds the current-event listing
	 * 
	 * @return array the listing:
	 * 	<code>
	 * 		array(
	 * 			'list' => array(
	 * 				array(
	 * 					'url' => ...,
	 * 					'title_complete' => ...,
	 * 					'title' => ...,
	 * 					'date' => ...
	 *				),
	 * 				...
	 * 			),
	 * 			'more' => true|false
	 * 		)
	 * 	</code>
	 */
	public static function get_current_events()
	{
		$cfg = FWS_Props::get()->cfg();
		$ev = self::_get_events();
		
		$denied = BS_ForumUtils::get_denied_forums(false);
		
		// events
		$events = array('list' => array(),'more' => $ev['more']);
		if(is_array($ev))
		{
			$durl = BS_URL::get_sub_url('calendar','eventdetails');
			
			foreach($ev['events'] as $edata)
			{
				if($edata['tid'] == 0)
				{
					// skip calendar-events, if disabled
					if($cfg['enable_calendar'] == 0)
						continue;
	
					$durl->set(BS_URL_ID,$edata['id']);
					$murl = $durl->to_url();
				}
				else
				{
					// is this forum denied for the user?
					if($cfg['hide_denied_forums'] == 1 && in_array($edata['rubrikid'],$denied))
						continue;
					
					$murl = BS_URL::build_posts_url($edata['rubrikid'],$edata['tid']);
				}
				
				list($titled,$titlec) = FWS_StringHelper::get_limited_string($edata['event_title'],15);
				$events['list'][] = array(
					'url' => $murl,
					'title_complete' => $titlec,
					'title' => $titled,
					'date' => FWS_Date::get_date($edata['event_begin'],false)
				);
			}
		}
		
		return $events;
	}
	
	/**
	 * Fetches the events from the database and returns them
	 *
	 * @return array the events:
	 * 	<code>array(
	 * 		'more' => ..., // wether there are more events
	 * 		'events' => array() // the events
	 * 	)</code>
	 */
	private static function _get_events()
	{
		$res = array();
		
		$events = BS_DAO::get_events()->get_next_events(
			BS_MINISTATS_EVENT_DAYS * 86400,BS_MINISTATS_MAX_EVENTS + 1
		);
		foreach($events as $data)
		{
			if(count($res) < BS_MINISTATS_MAX_EVENTS)
				$res[] = $data;
		}
		
		return array(
			'more' => count($events) > BS_MINISTATS_MAX_EVENTS,
			'events' => $res
		);
	}
	
	/**
	 * Builds the birthdays of today
	 * 
	 * @return array the birthday-listing:
	 * 	<code>
	 * 		array(
	 * 			'list' => array(
	 * 				array(
	 * 					'username' => ...,
	 * 					'age' => ...,
	 *				),
	 * 				...
	 * 			),
	 * 			'more' => true|false
	 * 		)
	 * 	</code>
	 */
	public static function get_todays_birthdays()
	{
		$ev = self::_get_birthdays();
		
		// birthdays
		$bds = array('list' => array(),'more' => $ev['more']);
		if(is_array($ev))
		{
			$current_year = FWS_Date::get_formated_date('Y');
			foreach($ev['birthdays'] as $edata)
			{
				$split = explode('-',$edata['add_birthday']);
				$bds['list'][] = array(
					'username' => BS_UserUtils::get_link($edata['id'],$edata['user_name']),
					'age' => ($current_year - $split[0])
				);
			}
		}
		
		return $bds;
	}
	
	/**
	 * Fetches the birthdays from the database and returns them
	 *
	 * @return array the birthdays:
	 * 	<code>array(
	 * 		'more' => ..., // wether there are more birthdays
	 * 		'birthdays' => array() // the birthdays
	 * 	)</code>
	 */
	private static function _get_birthdays()
	{
		$more = false;
		$month = FWS_Date::get_formated_date('m');
		$day = FWS_Date::get_formated_date('d');
		$userlist = BS_DAO::get_profile()->get_birthday_users(
			(int)$month,(int)$day,(BS_MINISTATS_MAX_EVENTS + 1)
		);
		
		if(count($userlist) == BS_MINISTATS_MAX_EVENTS)
		{
			$more = true;
			unset($userlist[count($userlist) - 1]);
		}
		
		return array(
			'more' => $more,
			'birthdays' => $userlist
		);
	}
}
?>