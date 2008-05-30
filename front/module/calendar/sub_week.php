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
	public function get_actions()
	{
		// TODO finish
		return array();
	}
	
	public function run()
	{
		$helper = BS_Front_Module_Calendar_Helper::get_instance();
		$week_start = $helper->get_week_timestamp();
		
		$back = $week_start - (86400 * 7);
		$back_url = $this->url->get_url(
			'calendar','&amp;'.BS_URL_LOC.'=week&amp;'.BS_URL_WEEK.'='.$back
		);
	
		$forward = $week_start + (86400 * 7);
		$forward_url = $this->url->get_url(
			'calendar','&amp;'.BS_URL_LOC.'=week&amp;'.BS_URL_WEEK.'='.$forward
		);
	
		$weekname = PLIB_Date::get_formated_date('W',$week_start);
		$year = PLIB_Date::get_formated_date('o',$week_start);
		$this->tpl->add_variables(array(
			'title' => sprintf($this->locale->lang('week_in_year'),$weekname,$year),
			'back' => $back_url,
			'forward' => $forward_url
		));
		
		$rows = array();
	
		$wd_detail = $helper->get_weekdays();
		$today = PLIB_Date::get_formated_date('dm');
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
	
					$weekno = PLIB_Date::get_formated_date('w',$ts);
					$rows[$l]['day_titles'][] = array(
						'name' => $wd_detail[$weekno].', '.PLIB_Date::get_formated_date('date',$ts)
					);
				}
			}
	
			for($d = 1;$d <= 4;$d++)
			{
				if($l == 0 || $d < 4)
				{
					$event_index = PLIB_Date::get_formated_date('dm',$timestamp);
					$border = $event_index == $today ? 'bs_calendar_border_today' : 'bs_calendar_border';
					
					$rows[$l]['days'][] = array(
						'event' => $helper->get_events_of($event_index,30,10000),
						'border' => $border
					);
				}
				$timestamp += 86400;
			}
		}
		
		$this->tpl->add_array('rows',$rows);
	}
	
	public function get_location()
	{
		// TODO finish
		return array();
	}
}
?>