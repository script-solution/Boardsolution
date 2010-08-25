<?php
/**
 * Contains the helper-class for the calendar
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The helper-class for the calendar
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_Calendar_Helper extends FWS_Singleton
{
	/**
	 * @return BS_Front_Module_Calendar_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * The month-names
	 *
	 * @var array
	 */
	private $_months = null;
	
	/**
	 * The weekdays in short form
	 *
	 * @var array
	 */
	private $_weekdays_short = null;
	
	/**
	 * The weekdays
	 *
	 * @var array
	 */
	private $_weekdays = null;
	
	/**
	 * The timestamp to display a week
	 *
	 * @var int
	 */
	private $_week = null;
	
	/**
	 * The month that should be displayed
	 *
	 * @var int
	 */
	private $_month = null;
	
	/**
	 * The year that should be displayed
	 *
	 * @var int
	 */
	private $_year = null;
	
	/**
	 * All birthdays
	 *
	 * @var array
	 */
	private $_birthdays = null;
	
	/**
	 * All events
	 *
	 * @var array
	 */
	private $_events = null;
	
	/**
	 * @return array an array with all month-names
	 */
	public function get_months()
	{
		$locale = FWS_Props::get()->locale();

		if($this->_months === null)
		{
			$this->_months = array(
				1 => $locale->lang('january'),
				$locale->lang('february'),
				$locale->lang('march'),
				$locale->lang('april'),
				$locale->lang('may'),
				$locale->lang('june'),
				$locale->lang('july'),
				$locale->lang('august'),
				$locale->lang('september'),
				$locale->lang('october'),
				$locale->lang('november'),
				$locale->lang('december')
			);
		}
		return $this->_months;
	}
	
	/**
	 * @return array an array with all weekday-names (the shortcut)
	 */
	public function get_weekdays_short()
	{
		$locale = FWS_Props::get()->locale();

		if($this->_weekdays_short === null)
		{
			$names = array('su','mo','tu','we','th','fr','sa');
			$ts = $this->get_weekstart('now');
			$this->_weekdays_short = array();
			for($i = 0; $i < count($names); $i++)
			{
				$wday = FWS_Date::get_formated_date('w',$ts);
				$this->_weekdays_short[$wday] = $locale->lang($names[$wday]);
				$ts += 86400;
			}
		}
		return $this->_weekdays_short;
	}
	
	/**
	 * @return array an array with all weekday-names
	 */
	public function get_weekdays()
	{
		$locale = FWS_Props::get()->locale();

		if($this->_weekdays === null)
		{
			$names = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
			$ts = $this->get_weekstart('now');
			$this->_weekdays = array();
			for($i = 0; $i < count($names); $i++)
			{
				$wday = FWS_Date::get_formated_date('w',$ts);
				$this->_weekdays[$wday] = $locale->lang($names[$wday]);
				$ts += 86400;
			}
		}
		return $this->_weekdays;
	}
	
	/**
	 * Determines the timestamp that should be used to display a week
	 *
	 * @return int the timestamp
	 */
	public function get_week_timestamp()
	{
		$input = FWS_Props::get()->input();

		if($this->_week === null)
		{
			$uday = $input->get_var(BS_URL_DAY,'get',FWS_Input::INTEGER);
			$uweek = $input->get_var(BS_URL_WEEK,'get',FWS_Input::INTEGER);
			
			// ensure that all parameters are valid
			$this->_week = $uday !== null ? $uday : $uweek;
			if(!FWS_Date::is_valid_timestamp($this->_week))
				$this->_week = $this->get_weekstart(time());
		}
		
		return $this->_week;
	}
	
	/**
	 * Determines the start of the week from the given timestamp
	 *
	 * @param int $ts the timestamp
	 * @param int $timezone the desired timezone for 0:00 on the weekstart
	 * @return int the week-start-timestamp
	 */
	private function get_weekstart($ts,$timezone = FWS_Date::TZ_GMT)
	{
		$year = FWS_Date::get_formated_date('Y',$ts,FWS_Date::TZ_GMT,FWS_Date::TZ_GMT);
		$month = FWS_Date::get_formated_date('m',$ts,FWS_Date::TZ_GMT,FWS_Date::TZ_GMT);
		$day = FWS_Date::get_formated_date('d',$ts,FWS_Date::TZ_GMT,FWS_Date::TZ_GMT);
		$weekday = FWS_Date::get_formated_date('w',$ts,FWS_Date::TZ_GMT,FWS_Date::TZ_GMT);
		$weekday = $weekday == 0 ? 6 : $weekday - 1;
		return FWS_Date::get_timestamp(
			array(0,0,0,$month,$day,$year),$timezone,'-'.$weekday.'days'
		);
	}
	
	/**
	 * Determines the date that should be displayed.
	 *
	 * @return array an array of the form: <code>array(<year>,<month>)</code>
	 */
	public function get_date()
	{
		$input = FWS_Props::get()->input();

		if($this->_month === null)
		{
			$month = $input->get_var(BS_URL_MONTH,'get',FWS_Input::INTEGER);
			$year = $input->get_var(BS_URL_YEAR,'get',FWS_Input::INTEGER);
			
			// no month and year given but day/week instead?
			if($month == null && $year == null)
			{
				$day = $input->get_var(BS_URL_DAY,'get',FWS_Input::INTEGER);
				$week = $input->get_var(BS_URL_WEEK,'get',FWS_Input::INTEGER);
				$ts = $day != null ? $day : $week;
				if(!FWS_Date::is_valid_timestamp($ts))
					$ts = time();
				
				$month = FWS_Date::get_formated_date('m',$ts);
				$year = FWS_Date::get_formated_date('Y',$ts);
			}
		
			// ensure that all parameters are valid
			if($month == null || $month <= 0 || $month > 12 || $year == null ||
				 $year >= 2037 || $year <= 1970)
			{
				$month = FWS_Date::get_formated_date('m');
				$year = FWS_Date::get_formated_date('Y');
			}
			
			$this->_month = $month;
			$this->_year = $year;
		}
		
		return array($this->_year,$this->_month);
	}
	
	/**
	 * Builds an array with all birthdays of the selected month and year
	 *
	 * @return array an array of the form:
	 * 	<code>
	 * 	array(
	 * 		array(
	 * 			'user_id' => <id>,
	 * 			'user_name' => <name>,
	 * 			'user_group' => <grouplist>,
	 * 			'age' => <age>
	 * 		),
	 * 		...
	 * 	)
	 * 	</code>
	 */
	public function get_birthdays()
	{
		if($this->_birthdays === null)
		{
			list($year,$month) = $this->get_date();
			list(,$last_month) = $this->get_relative_date($month,$year,-1);
			list(,$next_month) = $this->get_relative_date($month,$year,1);
			
			$this->_birthdays = array();
			$months = array($month,$last_month,$next_month);
			foreach(BS_DAO::get_profile()->get_birthday_users_in_months($months) as $daten)
			{
				$split = explode('-',$daten['add_birthday']);
				$age = $this->get_user_age($month,$year,$split);
				if(!isset($this->_birthdays[$split[2].$split[1]]))
					$this->_birthdays[$split[2].$split[1]] = array();
				
				$this->_birthdays[$split[2].$split[1]][] = array(
					'user_id' => $daten['id'],
					'user_name' => $daten['user_name'],
					'user_group' => $daten['user_group'],
					'age' => $age
				);
			}
		}
		
		return $this->_birthdays;
	}
	
	/**
	 * Builds an array of all events for the selected month and year
	 *
	 * @return array an array of the form:
	 * 	<code>
	 * 	array(
	 * 		array(
	 * 			'id' => <eventId>,
	 * 			'tid' => <topicId>,
	 * 			'rid' => <forumId>,
	 * 			'name' => <eventName>
	 * 		),
	 * 		...
	 * 	)
	 * 	</code>
	 */
	public function get_events()
	{
		$cfg = FWS_Props::get()->cfg();

		if($this->_events === null)
		{
			$denied = array();
			if($cfg['hide_denied_forums'] == 1)
				$denied = BS_ForumUtils::get_denied_forums(false);
			
			list($year,$month) = $this->get_date();
			
			$this->_events = array();
			$min_date = FWS_Date::get_timestamp(
				array(0,0,0,$month,1,$year),FWS_Date::TZ_USER,'-1month'
			);
			$max_date = FWS_Date::get_timestamp(
				array(0,0,0,$month,1,$year),FWS_Date::TZ_USER,'+2months'
			);
			foreach(BS_DAO::get_events()->get_events_between($min_date,$max_date,$denied) as $data)
			{
				$date_key = FWS_Date::get_formated_date('dmY',$data['event_begin']);
				$array = array(
					'id' => $data['id'],
					'tid' => $data['tid'],
					'rid' => $data['rubrikid'],
					'name' => $data['event_title']
				);
				
				if(isset($this->_events[$date_key]))
					$this->_events[$date_key][] = $array;
				else
					$this->_events[$date_key] = array($array);
				
				$end_key = $data['event_end'] > 0 ? FWS_Date::get_formated_date('dmY',$data['event_end']) : 0;
				if($data['event_end'] > 0 && $end_key != $date_key)
				{
					$day = FWS_String::substr($date_key,0,2);
					$month = FWS_String::substr($date_key,2,2);
					$year = FWS_String::substr($date_key,4,4);
					for($i = 1;$end_key != $date_key;$i++)
					{
						$nextdate = new FWS_Date(array(0,0,0,$month,$day,$year));
						$nextdate->modify('+'.$i.'days');
						$next = $nextdate->to_timestamp();
						$date_key = FWS_Date::get_formated_date('dmY',$next);
						if(isset($this->_events[$date_key]))
							$this->_events[$date_key][] = $array;
						else
							$this->_events[$date_key] = array($array);
					}
				}
			}
		}
		
		return $this->_events;
	}
	
	/**
	 * Checks wether the given date has at least one event or at least one birthday
	 *
	 * @param string $date the date in the format <var><day><month></var>
	 * @return boolean true if so
	 */
	public function has_entries($date)
	{
		// prevent function-calling here
		if($this->_birthdays !== null)
			$birthdays = $this->_birthdays;
		else
			$birthdays = $this->get_birthdays();
		
		if($this->_events !== null)
			$events = $this->_events;
		else
			$events = $this->get_events();
		
		if($this->_month === null)
			list($year,) = $this->get_date();
		else
			$year = $this->_year;
		
		return isset($birthdays[$date]) || isset($events[$date.$year]);
	}
	
	/**
	 * Collects all events of the given date
	 *
	 * @param string $date the date in the format <var><day><month></var>
	 * @param int $max_name_len the maximum length of names
	 * @param int $max_events the maximum number of events per day
	 * @return string the html-code for the events and birthdays
	 */
	public function get_events_of($date,$max_name_len = 10,$max_events = 3)
	{
		$birthdays = $this->get_birthdays();
		$events = $this->get_events();
		
		$count = 0;
		$res = array('bd' => array(),'ev' => array(),'toomany' => '');
		if(isset($birthdays[$date]))
		{
			foreach($birthdays[$date] as $content)
			{
				// skip user that are not born yet :)
				if($content['age'] < 0)
					continue;
				
				if($count >= $max_events)
				{
					$count++;
					break;
				}
	
				$username_strip = $content['user_name'];
				$username_len = FWS_String::strlen($username_strip);
				if($username_len > $max_name_len)
				{
					$username = FWS_String::substr($username_strip,0,$max_name_len - 2).'...';
					$title = '" title="'.$username_strip;
				}
				else
				{
					$username = $username_strip;
					$title = '';
				}
				
				$user_link = BS_UserUtils::get_link(
					$content['user_id'],$username,$content['user_group'],false,$title
				);
				
				$res['bd'][] = array(
					'age' => $content['age'],
					'username' => $user_link
				);
				$count++;
			}
		}
	
		list($year,) = $this->get_date();
		if(isset($events[$date.$year]))
		{
			$posts_url = BS_URL::get_mod_url('posts');
			$cal_url = BS_URL::get_sub_url('calendar','eventdetails');
			
			foreach($events[$date.$year] as $content)
			{
				if($count >= $max_events)
				{
					$count++;
					break;
				}
	
				$name = $content['name'];
				$name_strip = $name;
				$name_len = FWS_String::strlen($name_strip);
				if($name_len > $max_name_len)
					$name_strip = FWS_String::substr($name,0,$max_name_len - 2).'...';
	
				if($content['tid'])
				{
					$posts_url->set(BS_URL_FID,$content['rid']);
					$posts_url->set(BS_URL_TID,$content['tid']);
					$purl = $posts_url->to_url();
				}
				else
				{
					$cal_url->set(BS_URL_ID,$content['id']);
					$purl = $cal_url->to_url();
				}
				
				$res['ev'][] = array(
					'name_complete' => $name,
					'name' => $name_strip,
					'url' => $purl
				);
				$count++;
			}
		}
	
		if($count > $max_events)
		{
			$loc = FWS_Date::get_timestamp(array(
				0,0,0,
				FWS_String::substr($date,2,2),
				FWS_String::substr($date,0,2),
				$year
			));
			$curl = BS_URL::get_sub_url('calendar','week');
			$curl->set(BS_URL_DAY,$loc);
			$res['toomany'] = $curl->to_url();
		}
		
		return $res;
	}
	
	/**
	 * Determines the month-offset for the given timestamp
	 * this is used to calculate the number of "blank" days in a week
	 *
	 * @param int $timestamp the timestamp
	 * @return int the offset
	 */
	public function get_month_offset($timestamp)
	{
		$weekstart = $this->get_weekstart($timestamp);
		$wstartday = FWS_Date::get_formated_date('w',$weekstart);
		$curday = FWS_Date::get_formated_date('w',$timestamp);
		if($wstartday == 1)
			return ($curday == 0 ? 7 : $curday) - 1;
		else
			return ($curday == 0 ? 7 : $curday) % 7;
	}
	
	/**
	 * Builds a date with the given offset
	 *
	 * @param int $month the month
	 * @param int $year the year
	 * @param int $a the number of months you want to walk forward/backwards
	 * @return array an array of the form: <code>array(<year>,<month>)</code>
	 */
	public function get_relative_date($month,$year,$a)
	{
		if($a == -1 && $month == 1)
			return array($year - 1,12);
	
		if($a == 1 && $month == 12)
			return array($year + 1,1);
	
		return array($year,$month + $a);
	}
	
	/**
	 * Calculates the user-age
	 *
	 * @param int $month the month
	 * @param int $year the year
	 * @param array $birthday an array of the form: <code>array(<day>,<month>,<year>)</code>
	 * @return int the age in years
	 */
	public function get_user_age($month,$year,$birthday)
	{
		if($birthday[1] == $month)
			return $year - $birthday[0];
	
		if($month == 12 && $birthday[1] == 1)
			return ($year + 1) - $birthday[0];
	
		if($month == 1 && $birthday[1] == 12)
			return ($year - 1) - $birthday[0];
	
		return $year - $birthday[0];
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>
