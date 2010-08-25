<?php
/**
 * Contains the month-calendar-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The month submodule for module calendar
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_calendar_month extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();

		$helper = BS_Front_Module_Calendar_Helper::get_instance();
		list($year,$month) = $helper->get_date();
		
		$sel_ts = FWS_Date::get_timestamp(array(0,0,0,$month,1,$year),FWS_Date::TZ_USER);
		$mon_len = FWS_Date::get_formated_date('t',$sel_ts);
		list($prevyear,$prevmonth) = $helper->get_relative_date($month,$year,-1);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_YEAR,$prevyear);
		$url->set(BS_URL_MONTH,$prevmonth);
		$back_url = $url->to_url();
	
		list($nextyear,$nextmonth) = $helper->get_relative_date($month,$year,1);
		$url->set(BS_URL_YEAR,$nextyear);
		$url->set(BS_URL_MONTH,$nextmonth);
		$forward_url = $url->to_url();

		$weekdays = $helper->get_weekdays();
		// the indices are the weekday-numbers, i.e. sunday=0 etc., but there are in the order
		// depending on the timezone (monday first in germany, ...)
		// therefore we pass array_values() to the template
		$tpl->add_variables(array('wd_detail' => array_values($weekdays)));
		
		$months = $helper->get_months();
		$tpl->add_variables(array(
			'title' => $months[abs($month)].' '.$year,
			'back' => $back_url,
			'forward' => $forward_url,
			'view_add_event' => $cfg['enable_calendar_events'] &&
				($cfg['display_denied_options'] || $auth->has_global_permission('add_cal_event'))
		));
		
		$weeks = array();
		$today = FWS_Date::get_formated_date('date');
		$month_offset = $helper->get_month_offset($sel_ts);
		$day = 1;
		$week = FWS_Date::get_timestamp(
			array(0,0,0,$month,1,$year),FWS_Date::TZ_USER,'-'.$month_offset.'days'
		);
		$weekurl = BS_URL::get_sub_url(0,'week');
		for($w = 0;$w < 6;$w++)
		{
			$weeks[$w] = array();
			$weeks[$w]['url'] = $weekurl->set(BS_URL_WEEK,$week)->to_url();
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
					$days_ts = FWS_Date::get_timestamp(array(0,0,0,$month,$day,$year));
					$sday = sprintf('%02d',$day);
					$smonth = sprintf('%02d',$month);
					$birthday_index = $sday.$smonth;
					$days = FWS_Date::get_formated_date('date',$days_ts);
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
		
		$tpl->add_variable_ref('weeks',$weeks);
	}
}
?>
