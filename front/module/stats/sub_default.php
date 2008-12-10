<?php
/**
 * Contains the default-stats-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default submodule for module stats
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_stats_default extends BS_Front_SubModule
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
		
		$renderer->add_breadcrumb($locale->lang('general'),BS_URL::build_mod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		$limit = 10;
		$time = time();

		########################## general ##########################

		$stats_data = $functions->get_stats();
		$tpl->add_variable_ref('general',$stats_data);
		
		$stats = array();
		
		########################## Logins ##########################
		
		$login_stats = array(
			'title' => $locale->lang('logins'),
			'align' => 'left',
			'new_row' => false,
			'data' => array()
		);
		
		foreach(BS_DAO::get_profile()->get_users('p.logins','DESC',0,$limit) as $x => $data)
		{
			if($x == 0)
				$max = $data['logins'];

			$percent = $data['logins'] == 0 ? 0 : round(100 / ($max / $data['logins']),2);
			$text = $data['logins'].' '.($data['logins'] == 1 ? $locale->lang('login') : $locale->lang('logins'));

			$login_stats['data'][] = $this->_get_stats_data(
				$x,$percent,$data['id'],$data['user_name'],$data['user_group'],$text
			);
		}

		$stats[] = $login_stats;
		
		########################## lastlogin ##########################
		
		$lastlogin_stats = array(
			'title' => $locale->lang('lastlogin'),
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
				$text = FWS_Date::get_date(time() - $seconds);
				$lastlogin_stats['data'][] = $this->_get_stats_data(
					$i,$val,$userlist[$i]['id'],$userlist[$i]['user_name'],$userlist[$i]['user_group'],$text
				);
			}
		}
		
		$stats[] = $lastlogin_stats;

		########################## posts ##########################
		
		if($cfg['enable_post_count'] == 1)
		{
			$posts_stats = array(
				'title' => $locale->lang('posts'),
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
				$text .= ($data['posts'] == 1) ? $locale->lang('post') : $locale->lang('posts');
				
				$posts_stats['data'][] = $this->_get_stats_data(
					$i,$percent,$data['id'],$data['user_name'],$data['user_group'],$text
				);
			}
			
			$stats[] = $posts_stats;

			########################## topics ##########################
			
			$points_stats = array(
				'title' => $locale->lang('threads'),
				'align' => 'right',
				'new_row' => true,
				'data' => array()
			);

			foreach(BS_DAO::get_topics()->get_topic_creation_stats($limit) as $i => $data)
			{
				if($i == 0)
					$max = $data['num'];

				$percent = $data['num'] == 0 ? 0 : round(100 / ($max / $data['num']),2);
				$text = $data['num'].' ';
				$text .= ($data['num'] == 1) ? $locale->lang('thread') : $locale->lang('threads');

				$points_stats['data'][] = $this->_get_stats_data(
					$i,$percent,$data['user_id'],$data['user_name'],$data['user_group'],$text
				);
			}
			
			$stats[] = $points_stats;

			########################## posts per day ##########################
			
			$perday_stats = array(
				'title' => $locale->lang('postsperday'),
				'align' => 'left',
				'new_row' => false,
				'data' => array()
			);

			foreach(BS_DAO::get_profile()->get_users_stats_postsperday($limit) as $i => $data)
			{
				if($i == 0)
					$max = $data['per_day'];
				
				$percent = $data['per_day'] == 0 || $max == 0 ? 0 : round(100 / ($max / $data['per_day']),2);
				$text = round($data['per_day'],2).' '.$locale->lang('postsperday');

				$perday_stats['data'][] = $this->_get_stats_data(
					$i,$percent,$data['id'],$data['user_name'],$data['user_group'],$text
				);
			}
			
			$stats[] = $perday_stats;

			########################## points ##########################
			
			$points_stats = array(
				'title' => $locale->lang('points'),
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
				$text .= ($data['exppoints'] == 1) ? $locale->lang('point') : $locale->lang('points');

				$points_stats['data'][] = $this->_get_stats_data(
					$i,$percent,$data['id'],$data['user_name'],$data['user_group'],$text
				);
			}
			
			$stats[] = $points_stats;
		}

		$surl = '';
		if($user->is_loggedin() && $cfg['enable_post_count'] == 1)
		{
			$url = BS_URL::get_standalone_url('stats_diagram');
			$url->set('id',$user->get_user_id());
			$url->set('key',md5($user->get_session_id().$user->get_user_ip()));
			$surl = $url->to_url();
		}
		
		$tpl->add_variables(array(
			'own_stats_image' => $surl
		));
		
		$tpl->add_variable_ref('stats',$stats);
	}

	/**
	 * Builds the necessary data for one row
	 *
	 * @param int $row the row-number
	 * @param int|float $percent the percent-value
	 * @param int $user_id the id of the user
	 * @param string $username the name of the user
	 * @param string $user_groups the usergroups of the user
	 * @param string $text the text-value
	 */
	private function _get_stats_data($row,$percent,$user_id,$username,$user_groups,$text)
	{
		$user = FWS_Props::get()->user();
		
		$x_var = $row + 1;
		$username = BS_UserUtils::get_link($user_id,$username,$user_groups);
		$img_percent = round($percent,0);

		$stats = array(
			'x_var' => $x_var,
			'user' => $username,
			'is_current' => $user->get_user_id() == $user_id,
			'text' => $text,
			'img_percent' => $img_percent,
			'img_remaining_percent' => 100 - $img_percent,
			'img_width' => ($img_percent > 0 ? '100%' : '0px')
		);
		return $stats;
	}
}
?>