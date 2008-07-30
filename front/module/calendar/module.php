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
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($cfg['enable_calendar'] == 1 && $auth->has_global_permission('view_calendar'));
		
		$renderer->add_breadcrumb($locale->lang('calendar'),BS_URL::get_url());
		$renderer->set_template('calendar.htm');
		$renderer->add_action(BS_ACTION_CAL_DEL_EVENT,'deleteevent');
		
		// init submodule
		$this->_sub->init($doc);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();

		// run submodule
		parent::run();
		
		$helper = BS_Front_Module_Calendar_Helper::get_instance();
		list($year,$month) = $helper->get_date();
		
		// generate hidden-fields
		$hidden_fields = array();
		$hidden_fields[BS_URL_ACTION] = $input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING);
		if(($sid = BS_URL::get_session_id()) !== false)
			$hidden_fields[$sid[0]] = $sid[1];
		$url = new BS_URL();
		$extern = $url->get_extern_vars();
		$hidden_fields = array_merge($hidden_fields,$extern);
	
		$years = array();
		for($y = 1990;$y <= 2020;$y++)
			$years[$y] = $y;
		
		$form = $this->request_formular(false,false);
		$tpl->add_variables(array(
			'target' => $input->get_var('PHP_SELF','server',FWS_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'month_combo' => $form->get_combobox(BS_URL_MONTH,$helper->get_months(),$month),
			'year_combo' => $form->get_combobox(BS_URL_YEAR,$years,$year),
			'view_add_event' => $cfg['enable_calendar_events'] &&
				($cfg['display_denied_options'] || $auth->has_global_permission('add_cal_event')),
			'add_event_url' => BS_URL::get_url(0,'&amp;'.BS_URL_LOC.'=editevent'),
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
		$tpl = FWS_Props::get()->tpl();
		$helper = BS_Front_Module_Calendar_Helper::get_instance();
		$monthdata = array();
		
		$day_ts = FWS_Date::get_timestamp(array(0,0,0,$month,1,$year));
		$mon_len = FWS_Date::get_formated_date('t',$day_ts);
	
		$wd_short = $helper->get_weekdays_short();
		$tpl->add_array('wd_short',$wd_short,false);
		
		$months = $helper->get_months();
		$monthdata['url'] = BS_URL::get_url('calendar','&amp;'.BS_URL_MONTH.'='.$month
			.'&amp;'.BS_URL_YEAR.'='.$year);
		$monthdata['title'] = $months[abs($month)].' '.$year;
		$monthdata['weeks'] = array();
		
		$daybaseurl = BS_URL::get_url('calendar','&amp;'.BS_URL_LOC.'=week&amp;'.BS_URL_DAY.'=');
		$weekbaseurl = BS_URL::get_url('calendar','&amp;'.BS_URL_LOC.'=week&amp;'.BS_URL_WEEK.'=');
		$today = FWS_Date::get_formated_date('jnY');
		$month_offset = $helper->get_month_offset(FWS_Date::get_formated_date('w',$day_ts));
		$day = 1;
		$weektime = FWS_Date::get_timestamp(
			array(0,0,0,$month,1,$year),FWS_Date::TZ_USER,'-'.$month_offset.'days'
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
					// don't use FWS_StringHelper::ensure_2_digits() here (too many calls)
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