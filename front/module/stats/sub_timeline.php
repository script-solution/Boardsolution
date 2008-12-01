<?php
/**
 * Contains the timeline-stats-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The timeline submodule for module stats
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_stats_timeline extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$url = BS_URL::get_sub_url('stats','timeline');
		$renderer->add_breadcrumb($locale->lang('stats_timeline'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		
		$time_stats = $this->get_timeline_data();
		$max = $this->get_timeline_max($time_stats);
		$time_stats = $this->finalize_timeline($time_stats,$max);
		
		$tpl->add_array('timeline',$time_stats);
	}
	
	/**
	 * Grabs the data for the timeline from the DB
	 * 
	 * @return array the data
	 */
	private function get_timeline_data()
	{
		// use a big timestamp to get a value we'll "never" reach
		$min = mktime(0,0,0,1,1,2038);
		$time_stats = array();
		foreach(BS_DAO::get_posts()->get_post_stats_grouped_by_date() as $data)
		{
			if($data['post_time'] < $min)
				$min = $data['post_time'];
			
			$ydate = FWS_String::substr($data['date'],0,4);
			if(!isset($time_stats[$ydate]['total']['posts']))
				$time_stats[$ydate]['total']['posts'] = $data['num'];
			else
				$time_stats[$ydate]['total']['posts'] += $data['num'];
			
			$mdate = FWS_String::substr($data['date'],4);
			if(!isset($time_stats[$ydate][$mdate]['posts']))
				$time_stats[$ydate][$mdate]['posts'] = $data['num'];
			else
				$time_stats[$ydate][$mdate]['posts'] += $data['num'];
		}
		
		foreach(BS_DAO::get_topics()->get_topic_stats_grouped_by_date() as $data)
		{
			if($data['post_time'] < $min)
				$min = $data['post_time'];
			
			$ydate = FWS_String::substr($data['date'],0,4);
			if(!isset($time_stats[$ydate]['total']['topics']))
				$time_stats[$ydate]['total']['topics'] = $data['num'];
			else
				$time_stats[$ydate]['total']['topics'] += $data['num'];
			
			$mdate = FWS_String::substr($data['date'],4);
			if(!isset($time_stats[$ydate][$mdate]['topics']))
				$time_stats[$ydate][$mdate]['topics'] = $data['num'];
			else
				$time_stats[$ydate][$mdate]['topics'] += $data['num'];
		}
		
		foreach(BS_DAO::get_profile()->get_users_stats_grouped_by_regdate() as $data)
		{
			if($data['registerdate'] < $min)
				$min = $data['registerdate'];
			
			$ydate = FWS_String::substr($data['date'],0,4);
			if(!isset($time_stats[$ydate]['total']['user']))
				$time_stats[$ydate]['total']['user'] = $data['num'];
			else
				$time_stats[$ydate]['total']['user'] += $data['num'];
			
			$mdate = FWS_String::substr($data['date'],4);
			if(!isset($time_stats[$ydate][$mdate]['user']))
				$time_stats[$ydate][$mdate]['user'] = $data['num'];
			else
				$time_stats[$ydate][$mdate]['user'] += $data['num'];
		}
		
		// ensure that all months and years are in the array
		$cy = FWS_Date::get_formated_date('Y');
		$cm = FWS_Date::get_formated_date('n');
		$y = FWS_Date::get_formated_date('Y',$min);
		$m = FWS_Date::get_formated_date('n',$min);
		while($y < $cy || ($y == $cy && $m <= $cm))
		{
			if(!isset($time_stats[$y]['total']['posts']))
				$time_stats[$y]['total']['posts'] = 0;
			if(!isset($time_stats[$y]['total']['topics']))
				$time_stats[$y]['total']['topics'] = 0;
			if(!isset($time_stats[$y]['total']['user']))
				$time_stats[$y]['total']['user'] = 0;
			
			if(!isset($time_stats[$y][$m]['posts']))
				$time_stats[$y][$m]['posts'] = 0;
			if(!isset($time_stats[$y][$m]['topics']))
				$time_stats[$y][$m]['topics'] = 0;
			if(!isset($time_stats[$y][$m]['user']))
				$time_stats[$y][$m]['user'] = 0;
			
			$m++;
			if($m > 12)
			{
				$y++;
				$m = 1;
			}
		}

		// ensure that they are sorted correctly
		krsort($time_stats);
		foreach(array_keys($time_stats) as $year)
			krsort($time_stats[$year]);
		
		return $time_stats;
	}
	
	/**
	 * Determines the maximum of the given timeline-statas
	 * 
	 * @param array $time_stats the stats-data
	 * @return array an array with array('posts' => ...,'topics' => ...,'user' => ...)
	 */
	private function get_timeline_max($time_stats)
	{
		$max = array(
			'posts' => 0,
			'topics' => 0,
			'user' => 0
		);
		
		foreach($time_stats as $months)
		{
			foreach($months as $month => $types)
			{
				if($month != 'total')
				{
					foreach($types as $type => $count)
					{
						if($count > $max[$type])
							$max[$type] = $count;
					}
				}
			}
		}
		
		return $max;
	}
	
	/**
	 * Finalizes the timeline-data for the template. Calculates the percentual value and the
	 * average value for each year
	 * 
	 * @param array $time_stats the stats-data
	 * @param array $max the maximum for each type
	 * @return array the final stats-array
	 */
	private function finalize_timeline($time_stats,$max)
	{
		$final = array();
		foreach($time_stats as $year => $months)
		{
			foreach($months as $month => $types)
			{
				foreach($types as $type => $count)
				{
					if($month != 'total')
					{
						if($count == 0)
							$percent = 0;
						else
							$percent = (int)(100 / ($max[$type] / $count));
						
						$final[$year][$this->get_month_name($month)][$type] = array(
							'count' => $count,
							'percent' => $percent
						);
					}
					else
					{
						$avg = $time_stats[$year]['total'][$type] / (count($time_stats[$year]) - 1);
						$final[$year]['total'][$type] = $time_stats[$year]['total'][$type];
						$final[$year]['avg'][$type] = round($avg,2);
					}
				}
			}
		}
		
		return $final;
	}
	
	/**
	 * Returns the month-name in the selected language from the given timestamp
	 * 
	 * @param int $month the month-number
	 * @return string the month-name
	 */
	private function get_month_name($month)
	{
		$locale = FWS_Props::get()->locale();

		static $months = null;
		if($months === null)
		{
			$months = array(
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
		
		return $months[(int)$month];
	}
}
?>