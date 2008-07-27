<?php
/**
 * Contains the calendar-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The calendar-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_calendar extends BS_Front_SubModuleContainer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('calendar',array('month','week','eventdetails','editevent'),'month');
	}
	
	/**
	 * @see BS_Front_SubModuleContainer::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		
		$doc->set_has_access($cfg['enable_calendar'] == 1 && $auth->has_global_permission('view_calendar'));
		
		$doc->add_breadcrumb($locale->lang('calendar'),$url->get_url());
		$doc->set_template('calendar.htm');
		$doc->add_action(BS_ACTION_CAL_DEL_EVENT,'deleteevent');
		
		// init submodule
		$this->_sub->init($doc);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$url = PLIB_Props::get()->url();
		$tpl = PLIB_Props::get()->tpl();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();

		// run submodule
		parent::run();
		
		$helper = BS_Front_Module_Calendar_Helper::get_instance();
		list($year,$month) = $helper->get_date();
		
		// generate hidden-fields
		$hidden_fields = array();
		$hidden_fields[BS_URL_ACTION] = $input->get_var(BS_URL_ACTION,'get',PLIB_Input::STRING);
		if(($sid = $url->get_splitted_session_id()) != 0)
			$hidden_fields[$sid[0]] = $sid[1];
		$extern = $url->get_extern_vars();
		$hidden_fields = array_merge($hidden_fields,$extern);
	
		$years = array();
		for($y = 1990;$y <= 2020;$y++)
			$years[$y] = $y;
		
		$form = $this->request_formular(false,false);
		$tpl->add_variables(array(
			'target' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'month_combo' => $form->get_combobox(BS_URL_MONTH,$helper->get_months(),$month),
			'year_combo' => $form->get_combobox(BS_URL_YEAR,$years,$year),
			'view_add_event' => $cfg['enable_calendar_events'] &&
				($cfg['display_denied_options'] || $auth->has_global_permission('add_cal_event')),
			'add_event_url' => $url->get_url(0,'&amp;'.BS_URL_LOC.'=editevent'),
			'submoduletpl' => $this->_sub->get_template()
		));
	
		$months_small = array();
		for($a = -1;$a < 2;$a++)
		{
			list($syear,$smonth) = $helper->get_relative_date($month,$year,$a);
			$months_small[] = $this->_get_month_small($syear,$smonth);
		}
	
		$tpl->add_array('months',$months_small);
	}
	
	/**
	 * Displays a small version of the given month
	 *
	 * @param int $year the year to display
	 * @param int $month the month to display
	 * @return array the data
	 */
	private function _get_month_small($year,$month)
	{
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		$helper = BS_Front_Module_Calendar_Helper::get_instance();
		$monthdata = array();
		
		$day_ts = PLIB_Date::get_timestamp(array(0,0,0,$month,1,$year));
		$mon_len = PLIB_Date::get_formated_date('t',$day_ts);
	
		$wd_short = $helper->get_weekdays_short();
		$tpl->add_array('wd_short',$wd_short,false);
		
		$months = $helper->get_months();
		$monthdata['url'] = $url->get_url('calendar','&amp;'.BS_URL_MONTH.'='.$month
			.'&amp;'.BS_URL_YEAR.'='.$year);
		$monthdata['title'] = $months[abs($month)].' '.$year;
		$monthdata['weeks'] = array();
		
		$daybaseurl = $url->get_url('calendar','&amp;'.BS_URL_LOC.'=week&amp;'.BS_URL_DAY.'=');
		$weekbaseurl = $url->get_url('calendar','&amp;'.BS_URL_LOC.'=week&amp;'.BS_URL_WEEK.'=');
		$today = PLIB_Date::get_formated_date('jnY');
		$month_offset = $helper->get_month_offset(PLIB_Date::get_formated_date('w',$day_ts));
		$day = 1;
		$weektime = PLIB_Date::get_timestamp(
			array(0,0,0,$month,1,$year),PLIB_Date::TZ_USER,'-'.$month_offset.'days'
		);
		
		$events = $helper->get_events();
		$birthdays = $helper->get_birthdays();
		
		for($w = 0;$w < 6;$w++)
		{
			$monthdata['weeks'][$w] = array();
			$monthdata['weeks'][$w]['days'] = array();
			$monthdata['weeks'][$w]['url'] = $weekbaseurl.$weektime;
			
			$end_week = ($w * 7) + 7;
			for($d = ($w * 7) + 1;$d <= $end_week;$d++)
			{
				if(($w == 0 && $d > $month_offset) || ($w != 0 && $day <= $mon_len))
				{
					// don't use PLIB_StringHelper::ensure_2_digits() here (too many calls)
					$birth_index = $day < 10 ? '0'.$day : $day;
					$birth_index .= $month < 10 ? '0'.$month : $month;
					if(isset($events[$birth_index.$year]) || isset($birthdays[$birth_index]))
					{
						$days = '<a href="'.$daybaseurl.$day_ts.'">';
						$days .= $day.'</a>';
						$class = 'bs_calendar';
					}
					else
					{
						$days = $day;
						$class = 'bs_calendar_empty';
					}
	
					$day++;
					$day_ts += 86400;
		
					if(($day - 1).$month.$year == $today)
						$class .= '_today';
				}
				else
				{
					$days = '';
					$class = 'bs_calendar_empty';
				}
	
				$monthdata['weeks'][$w]['days'][] = array(
					'class' => $class,
					'days' => $days
				);
			}
	
			if($day > $mon_len)
				break;
	
			$weektime += 86400 * 7;
		}
		
		return $monthdata;
	}
}
?>