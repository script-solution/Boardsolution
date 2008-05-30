<?php
/**
 * Contains the stats-module
 * 
 * @version			$Id: module_stats.php 728 2008-05-22 22:09:30Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The stats-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_stats extends BS_Front_Module
{
	public function get_template()
	{
		$loc = $this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
		if($loc == 'timeline')
			return 'stats_timeline.htm';
		
		return 'stats.htm';
	}
	
	public function run()
	{
		$loc = $this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
		if($loc == 'timeline')
		{
			$time_stats = $this->get_timeline_data();
			$max = $this->get_timeline_max($time_stats);
			$time_stats = $this->finalize_timeline($time_stats,$max);
			
			$this->tpl->add_array('timeline',$time_stats);
		}
		else
		{
			$limit = 10;
			$time = time();
	
			########################## general ##########################
	
			$stats_data = $this->functions->get_stats();
			$this->tpl->add_array('general',$stats_data,false);
			
			$stats = array();
			
			########################## Logins ##########################
			
			$login_stats = array(
				'title' => $this->locale->lang('logins'),
				'align' => 'left',
				'new_row' => false,
				'data' => array()
			);
			
			foreach(BS_DAO::get_profile()->get_users('p.logins','DESC',0,$limit) as $x => $data)
			{
				if($x == 0)
					$max = $data['logins'];
	
				$percent = $data['logins'] == 0 ? 0 : round(100 / ($max / $data['logins']),2);
				$text = $data['logins'].' '.($data['logins'] == 1 ? $this->locale->lang('login') : $this->locale->lang('logins'));
	
				$login_stats['data'][] = $this->_get_stats_data(
					$x,$percent,$data['id'],$data['user_name'],$data['user_group'],$text
				);
			}
	
			$stats[] = $login_stats;
			
			########################## lastlogin ##########################
			
			$lastlogin_stats = array(
				'title' => $this->locale->lang('lastlogin'),
				'align' => 'right',
				'new_row' => true,
				'data' => array()
			);
			$userlist = BS_DAO::get_profile()->get_users('p.lastlogin','DESC',0,$limit);
	
			if(count($userlist) > 0)
			{
				$len = count($userlist);
				$min = $time - $userlist[$len - 1]['lastlogin'];
	
				for($i = 0;$i < $len;$i++)
				{
					$seconds = $time - $userlist[$i]['lastlogin'];
					$val = ($seconds == 0) ? 0 : round(100 / ($min / $seconds),2);
					$text = PLIB_Date::get_date(time() - $seconds);
					$lastlogin_stats['data'][] = $this->_get_stats_data(
						$i,$val,$userlist[$i]['id'],$userlist[$i]['user_name'],$userlist[$i]['user_group'],$text
					);
				}
			}
			
			$stats[] = $lastlogin_stats;
	
			########################## posts ##########################
			
			if($this->cfg['enable_post_count'] == 1)
			{
				$posts_stats = array(
					'title' => $this->locale->lang('posts'),
					'align' => 'left',
					'new_row' => false,
					'data' => array()
				);
	
				foreach(BS_DAO::get_profile()->get_users('p.posts','DESC',0,$limit) as $i => $data)
				{
					if($i == 0)
						$max = $data['posts'];

					$percent = $data['posts'] == 0 ? 0 : round(100 / ($max / $data['posts']),2);
					$text = $data['posts'].' ';
					$text .= ($data['posts'] == 1) ? $this->locale->lang('post') : $this->locale->lang('posts');
					
					$posts_stats['data'][] = $this->_get_stats_data(
						$i,$percent,$data['id'],$data['user_name'],$data['user_group'],$text
					);
				}
				
				$stats[] = $posts_stats;
	
				########################## points ##########################
				
				$points_stats = array(
					'title' => $this->locale->lang('points'),
					'align' => 'right',
					'new_row' => true,
					'data' => array()
				);
	
				foreach(BS_DAO::get_profile()->get_users('p.exppoints','DESC',0,$limit) as $i => $data)
				{
					if($i == 0)
						$max = $data['exppoints'];

					$percent = $data['exppoints'] == 0 ? 0 : round(100 / ($max / $data['exppoints']),2);
					$text = $data['exppoints'].' ';
					$text .= ($data['exppoints'] == 1) ? $this->locale->lang('point') : $this->locale->lang('points');

					$points_stats['data'][] = $this->_get_stats_data(
						$i,$percent,$data['id'],$data['user_name'],$data['user_group'],$text
					);
				}
				
				$stats[] = $points_stats;
	
				########################## posts per day ##########################
				
				$perday_stats = array(
					'title' => $this->locale->lang('postsperday'),
					'align' => 'left',
					'new_row' => false,
					'data' => array()
				);
	
				foreach(BS_DAO::get_profile()->get_users_stats_postsperday($limit) as $i => $data)
				{
					if($i == 0)
						$max = $data['per_day'];
					
					$percent = $data['per_day'] == 0 || $max == 0 ? 0 : round(100 / ($max / $data['per_day']),2);
					$text = round($data['per_day'],2).' '.$this->locale->lang('postsperday');

					$perday_stats['data'][] = $this->_get_stats_data(
						$i,$percent,$data['id'],$data['user_name'],$data['user_group'],$text
					);
				}
				
				$stats[] = $perday_stats;
			}
	
			########################## registered since ##########################
			
			$reg_stats = array(
				'title' => $this->locale->lang('registeredsince'),
				'align' => $this->cfg['enable_post_count'] == 1 ? 'right' : 'left',
				'new_row' => true,
				'data' => array()
			);
			
			foreach(BS_DAO::get_profile()->get_users('p.registerdate','ASC',0,$limit) as $i => $data)
			{
				if($i == 0)
					$max = ($time - $data['registerdate']);
	
				$percent = round(100 / ($max / ($time - $data['registerdate'])),2);
				$days = round((($time - $data['registerdate']) / (3600 * 24)),0);
				$text = $days.' '.(($days == 1) ? $this->locale->lang('day') : $this->locale->lang('days')).' , ';
				$text .= PLIB_Date::get_date($data['registerdate'],false);
				
				$reg_stats['data'][] = $this->_get_stats_data(
					$i,$percent,$data['id'],$data['user_name'],$data['user_group'],$text
				);
			}
			
			$stats[] = $reg_stats;
	
			$url = '';
			if($this->user->is_loggedin() && $this->cfg['enable_post_count'] == 1)
			{
				$key = md5($this->user->get_session_id().$this->user->get_user_ip());
				$url = $this->url->get_standalone_url(
					'front','stats_diagram','&amp;id='.$this->user->get_user_id().'&amp;key='.$key
				);
			}
			
			$this->tpl->add_variables(array(
				'own_stats_image' => $url
			));
			
			$this->tpl->add_array('stats',$stats,false);
		}
	}

	public function get_location()
	{
		$location = array();
		$loc = $this->input->get_var(BS_URL_LOC,'get',PLIB_Input::STRING);
		$location[$this->locale->lang('statistics')] = '';
		if($loc == 'timeline')
		{
			$location[$this->locale->lang('stats_timeline')] = $this->url->get_url(
				'stats','&amp;'.BS_URL_LOC.'=timeline'
			);
		}
		else
			$location[$this->locale->lang('general')] = $this->url->get_url('stats');
		
		return $location;
	}

	public function has_access()
	{
		return $this->cfg['enable_stats'] == 1 && $this->auth->has_global_permission('view_stats');
	}

	/**
	 * Builds the necessary data for one row
	 *
	 * @param int $row the row-number
	 * @param float $percent the percent-value
	 * @param int $user_id the id of the user
	 * @param string $username the name of the user
	 * @param string $user_groups the usergroups of the user
	 * @param string $text the text-value
	 */
	private function _get_stats_data($row,$percent,$user_id,$username,$user_groups,$text)
	{
		$x_var = $row + 1;
		$user = BS_UserUtils::get_instance()->get_link($user_id,$username,$user_groups);
		$img_percent = round($percent,0);

		$stats = array(
			'x_var' => $x_var,
			'user' => $user,
			'is_current' => $this->user->get_user_id() == $user_id,
			'text' => $text,
			'img_percent' => $img_percent,
			'img_remaining_percent' => 100 - $img_percent,
			'img_width' => ($img_percent > 0 ? '100%' : '0px')
		);
		return $stats;
	}
	
	/**
	 * Returns the month-name in the selected language from the given timestamp
	 * 
	 * @param int $month the month-number
	 * @return string the month-name
	 */
	private function get_month_name($month)
	{
		static $months = null;
		if($months === null)
		{
			$months = array(
				1 => $this->locale->lang('january'),
				$this->locale->lang('february'),
				$this->locale->lang('march'),
				$this->locale->lang('april'),
				$this->locale->lang('may'),
				$this->locale->lang('june'),
				$this->locale->lang('july'),
				$this->locale->lang('august'),
				$this->locale->lang('september'),
				$this->locale->lang('october'),
				$this->locale->lang('november'),
				$this->locale->lang('december')
			);
		}
		
		return $months[(int)$month];
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
			
			$ydate = PLIB_String::substr($data['date'],0,4);
			if(!isset($time_stats[$ydate]['total']['posts']))
				$time_stats[$ydate]['total']['posts'] = $data['num'];
			else
				$time_stats[$ydate]['total']['posts'] += $data['num'];
			
			$mdate = PLIB_String::substr($data['date'],4);
			if(!isset($time_stats[$ydate][$mdate]['posts']))
				$time_stats[$ydate][$mdate]['posts'] = $data['num'];
			else
				$time_stats[$ydate][$mdate]['posts'] += $data['num'];
		}
		
		foreach(BS_DAO::get_topics()->get_topic_stats_grouped_by_date() as $data)
		{
			if($data['post_time'] < $min)
				$min = $data['post_time'];
			
			$ydate = PLIB_String::substr($data['date'],0,4);
			if(!isset($time_stats[$ydate]['total']['topics']))
				$time_stats[$ydate]['total']['topics'] = $data['num'];
			else
				$time_stats[$ydate]['total']['topics'] += $data['num'];
			
			$mdate = PLIB_String::substr($data['date'],4);
			if(!isset($time_stats[$ydate][$mdate]['topics']))
				$time_stats[$ydate][$mdate]['topics'] = $data['num'];
			else
				$time_stats[$ydate][$mdate]['topics'] += $data['num'];
		}
		
		foreach(BS_DAO::get_profile()->get_users_stats_grouped_by_regdate() as $data)
		{
			if($data['registerdate'] < $min)
				$min = $data['registerdate'];
			
			$ydate = PLIB_String::substr($data['date'],0,4);
			if(!isset($time_stats[$ydate]['total']['user']))
				$time_stats[$ydate]['total']['user'] = $data['num'];
			else
				$time_stats[$ydate]['total']['user'] += $data['num'];
			
			$mdate = PLIB_String::substr($data['date'],4);
			if(!isset($time_stats[$ydate][$mdate]['user']))
				$time_stats[$ydate][$mdate]['user'] = $data['num'];
			else
				$time_stats[$ydate][$mdate]['user'] += $data['num'];
		}
		
		// ensure that all months and years are in the array
		$cy = PLIB_Date::get_formated_date('Y');
		$cm = PLIB_Date::get_formated_date('n');
		$y = PLIB_Date::get_formated_date('Y',$min);
		$m = PLIB_Date::get_formated_date('n',$min);
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
}
?>