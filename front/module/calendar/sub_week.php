<?php
/**
 * Contains the week-calendar-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The week submodule for module calendar
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_calendar_week extends BS_Front_SubModule
{
	public function run()
	{
		$url = FWS_Props::get()->url();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();

		$helper = BS_Front_Module_Calendar_Helper::get_instance();
		$week_start = $helper->get_week_timestamp();
		
		$back = $week_start - (86400 * 7);
		$back_url = $url->get_url(
			'calendar','&amp;'.BS_URL_LOC.'=week&amp;'.BS_URL_WEEK.'='.$back
		);
	
		$forward = $week_start + (86400 * 7);
		$forward_url = $url->get_url(
			'calendar','&amp;'.BS_URL_LOC.'=week&amp;'.BS_URL_WEEK.'='.$forward
		);
	
		$weekname = FWS_Date::get_formated_date('W',$week_start);
		$year = FWS_Date::get_formated_date('o',$week_start);
		$tpl->add_variables(array(
			'title' => sprintf($locale->lang('week_in_year'),$weekname,$year),
			'back' => $back_url,
			'forward' => $forward_url
		));
		
		$rows = array();
	
		$wd_detail = $helper->get_weekdays();
		$today = FWS_Date::get_formated_date('dm');
		$timestamp = $week_start;
		for($l = 0;$l < 2;$l++)
		{
			$rows[$l] = array();
			$rows[$l]['day_titles'] = array();
			$rows[$l]['days'] = array();
			
			for($d = 0;$d < 4;$d++)
			{
				if($l == 0 || $d < 3)
				{
					$ts = $timestamp + ($d * 86400);
	
					$weekno = FWS_Date::get_formated_date('w',$ts);
					$rows[$l]['day_titles'][] = array(
						'name' => $wd_detail[$weekno].', '.FWS_Date::get_formated_date('date',$ts),
						'timestamp' => $ts
					);
				}
			}
	
			for($d = 1;$d <= 4;$d++)
			{
				if($l == 0 || $d < 4)
				{
					$event_index = FWS_Date::get_formated_date('dm',$timestamp);
					$border = $event_index == $today ? 'bs_calendar_border_today' : 'bs_calendar_border';
					
					$rows[$l]['days'][] = array(
						'event' => $helper->get_events_of($event_index,30,10000),
						'border' => $border
					);
				}
				$timestamp += 86400;
			}
		}
		
		$tpl->add_array('rows',$rows);
	}
}
?>