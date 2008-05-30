<?php
/**
 * Contains the online-utils-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Some utility-methods for the online-list and other stuff
 *
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_OnlineUtils extends PLIB_Singleton
{
	/**
	 * @return BS_Front_OnlineUtils the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Adds a block with the currently online users
	 * 
	 * @param string $loc the location: forums, topics, posts
	 * @return string the html-code
	 */
	public function add_currently_online($loc = 'forums')
	{
		$online = $this->get_currently_online_user($loc);
		
		$this->tpl->set_template('inc_online_user.htm',0);
		$this->tpl->add_variables(array(
			'can_view_locations' => $this->auth->has_global_permission('view_online_locations'),
			'conclusion_list' => $online['conclusion'],
			'title' => $this->locale->lang('currently_online_'.$loc),
			'user_list' => $online['online_reg'],
			'legend' => $this->get_usergroup_legend()
		));
		$this->tpl->restore_template();
	}
	
	/**
	 * Gathers the information to display the currently online user
	 * 
	 * @param string $location the location: all, forums, topics, posts
	 * @return an array of the form:
	 * 	<code>
	 * 		array(
	 * 			'conclusion' => ...,
	 * 			'online_reg_num' => ...,
	 * 			'online_reg' => ...,
	 * 			'online_ghosts' => ...,
	 * 			'online_guests' => ...,
	 * 			'online_bot_num' => ...,
	 * 			'online_total' => ...
	 * 		);
	 * 	</code>
	 */
	public function get_currently_online_user($location = 'forums')
	{
		// user online
		$useronline = '';
		$guests = 0;
		$registered = 0;
		$ghosts = 0;
		$bots = 0;
		
		switch($location)
		{
			case 'all':
			case 'forums':
				$users = $this->sessions->get_user_at_location($location);
				break;
			case 'topics':
				$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
				$users = $this->sessions->get_user_at_location($location,$fid);
				break;
			case 'posts':
				$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
				$users = $this->sessions->get_user_at_location($location,$tid);
				break;
		}
		
		// sort user by date descending
		usort($users,array($this,'_sort_user_by_date_callback'));
		$permission_to_view_location = $this->auth->has_global_permission('view_online_locations');
		foreach($users as $daten)
		{
			if($daten['bot_name'] != '')
			{
				$bots++;
				$useronline .= $daten['bot_name'];
				if($daten['duplicates'] > 0)
					$useronline .= ' ('.($daten['duplicates'] + 1).'x)';
				$useronline .= ', ';
			}
			else if($daten['user_id'] == 0)
			{
				$guests++;
			}
			else if($this->user->is_admin() || $daten['ghost_mode'] == 0 ||
				$this->cfg['allow_ghost_mode'] == 0)
			{
				$location = '';
				if($permission_to_view_location)
				{
					$loc = new BS_Location($daten['location']);
					$location = $loc->decode();
					$location = strip_tags($location);
					$location = str_replace('"','&quot;',$location);
				}
	
				$time = strip_tags(PLIB_Date::get_date($daten['date']));
				$url = $this->url->get_url('userdetails','&amp;'.BS_URL_ID.'='.$daten['user_id']);
				$name = $this->auth->get_colored_username(
					$daten['user_id'],$daten['user_name'],$daten['user_group']
				);
				
				$useronline .= '<a title="'.$location.' ('.$time.')" href="'.$url.'">';
				$useronline .= $name.'</a>';
				if($daten['duplicates'] > 0)
					$useronline .= ' ('.($daten['duplicates'] + 1).'x)';
				$useronline .= ', ';
				
				$registered++;
			}
			else
			{
				$ghosts++;
			}
		}
		
		if($useronline != '')
			$user_names = PLIB_String::substr($useronline,0,PLIB_String::strlen($useronline) - 2);
		else
			$user_names = '<i>'.$this->locale->lang('none').'</i>';
		
		// build conclusion
		$conc = '<b>'.$registered.'</b> ';
		$conc .= ($registered == 1) ? $this->locale->lang('Registered1') : $this->locale->lang('Registered');
		if($this->cfg['allow_ghost_mode'] == 1)
		{
			$conc .= ', <b>'.$ghosts.'</b> ';
			$conc .= ($ghosts == 1) ? $this->locale->lang('hiddenuser1') : $this->locale->lang('hiddenuser');
		}
		$conc .= ', <b>'.$guests.'</b> ';
		$conc .= ($guests == 1) ? $this->locale->lang('guest') : $this->locale->lang('guests');
		$conc .= ', <b>'.$bots.'</b> ';
		$conc .= ($bots == 1) ? $this->locale->lang('bot') : $this->locale->lang('bots');
		
		return array(
			'conclusion' => $conc,
			'online_reg_num' => $registered,
			'online_reg' => $user_names,
			'online_ghosts' => $ghosts,
			'online_guests' => $guests,
			'online_bot_num' => $bots,
			'online_total' => ($registered + $ghosts + $guests + $bots)
		);
	}
	
	/**
	 * Builds the usergroup-legend for the online-lists
	 *
	 * @return string the legend
	 */
	public function get_usergroup_legend()
	{
		$legend = '';
		$groups = $this->cache->get_cache('user_groups');
		foreach($groups as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST && $gdata['is_visible'] == 1)
			{
				$gname = $this->auth->get_colored_groupname($gdata['id']);
				if($this->cfg['enable_memberlist'] == 1)
				{
					$url = $this->url->get_url(
						'memberlist',
						'&amp;'.BS_URL_MS_GROUP.urlencode('[]').'='.$gdata['id']
					);
					$legend .= '<a href="'.$url.'">'.$gname.'</a>, ';
				}
				else
					$legend .= $gname.', ';
			}
		}
		
		// add moderator
		if($this->cfg['enable_moderators'])
		{
			$url = $this->url->get_url('memberlist','&amp;'.BS_URL_MS_MODS.'=1');
			$legend .= '<a href="'.$url.'"><span style="color: #'.$this->cfg['mod_color'].';">';
			$legend .= $this->locale->lang('moderators').'</span></a>';
		}
		else
			$legend = PLIB_String::substr($legend,0,PLIB_String::strlen($legend) - 2);
		
		return $legend;
	}
	
	/**
	 * Builds the last-activity string
	 *
	 * @return string the string
	 */
	public function get_last_activity()
	{
		$last = $this->sessions->get_last_login();
		if($last['id'] != '' && $last['lastlogin'] > 0)
		{
			$lastlogin = PLIB_Date::get_date($last['lastlogin']).' '.$this->locale->lang('of').' ';
			$lastlogin .= BS_UserUtils::get_instance()->get_link($last['id'],$last['user_name'],$last['user_group']);
		}
		else
			$lastlogin = '';
		
		return $lastlogin;
	}
	
	/**
	 * The callback-function to sort the user-list by date descending
	 * 
	 * @param array $a the first user
	 * @param array $b the second user
	 * @return int -1 if $a is greater, 1 if $b is greater, 0 if equal
	 */
	private function _sort_user_by_date_callback($a,$b)
	{
		if($a['date'] > $b['date'])
			return -1;
		if($a['date'] < $b['date'])
			return 1;
		return 0;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>