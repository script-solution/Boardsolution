<?php
/**
 * Contains the month-calendar-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The month submodule for module calendar
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_calendar_month extends BS_Front_SubModule
{
	public function get_actions()
	{
		// TODO finish
		return array();
	}
	
	public function run()
	{
		$helper = BS_Front_Module_Calendar_Helper::get_instance();
		list($year,$month) = $helper->get_date();
		
		$sel_ts = PLIB_Date::get_timestamp(array(0,0,0,$month,1,$year));
		$mon_len = PLIB_Date::get_formated_date('t',$sel_ts);
		list($prevyear,$prevmonth) = $helper->get_relative_date($month,$year,-1);
		$back_url = $this->url->get_url(
			'calendar','&amp;'.BS_URL_YEAR.'='.$prevyear.'&amp;'.BS_URL_MONTH.'='.$prevmonth
		);
	
		list($nextyear,$nextmonth) = $helper->get_relative_date($month,$year,1);
		$forward_url = $this->url->get_url(
			'calendar','&amp;'.BS_URL_YEAR.'='.$nextyear.'&amp;'.BS_URL_MONTH.'='.$nextmonth
		);
	
		$weekdays = $helper->get_weekdays();
		$this->tpl->add_array('wd_detail',$weekdays);
		
		$months = $helper->get_months();
		$this->tpl->add_variables(array(
			'title' => $months[abs($month)].' '.$year,
			'back' => $back_url,
			'forward' => $forward_url
		));
		
		$weeks = array();
		$today = PLIB_Date::get_formated_date('date');
		$month_offset = $helper->get_month_offset(PLIB_Date::get_formated_date('w',$sel_ts));
		$day = 1;
		$week = PLIB_Date::get_timestamp(
			array(0,0,0,$month,1,$year),PLIB_Date::TZ_USER,'-'.$month_offset.'days'
		);
		for($w = 0;$w < 6;$w++)
		{
			$weeks[$w] = array();
			$weeks[$w]['url'] = $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=week&amp;'.BS_URL_WEEK.'='.$week);
			$weeks[$w]['days'] = array();
	
			$end_week = ($w * 7) + 7;
			for($d = ($w * 7) + 1;$d <= $end_week;$d++)
			{
				$event = '&nbsp;';
				$days = '';
				$border = '';
				$isempty = !(($w == 0 && $d > $month_offset) || ($w != 0 && $day <= $mon_len));
				$days_ts = 0;
				
				if(!$isempty)
				{
					$days_ts = PLIB_Date::get_timestamp(array(0,0,0,$month,$day,$year));
					$sday = PLIB_StringHelper::ensure_2_chars($day);
					$smonth = PLIB_StringHelper::ensure_2_chars($month);
					$birthday_index = $sday.$smonth;
					$days = PLIB_Date::get_formated_date('date',$days_ts);
					$day++;
					
					$event = $helper->get_events_of($birthday_index);
					$border = $days == $today ? 'bs_calendar_border_today' : 'bs_calendar_border';
				}
				
				$weeks[$w]['days'][] = array(
					'isempty' => $isempty,
					'border' => $border,
					'days' => $days,
					'event' => $event,
					'timestamp' => $days_ts
				);
			}
	
			if($day > $mon_len)
				break;
	
			$week += 86400 * 7;
		}
	
		$this->tpl->add_array('weeks',$weeks);
	}
	
	public function get_location()
	{
		// TODO finish
		return array();
	}
}
?>