<?php
/**
 * Contains the online-utils-class
 * 
 * @package			Boardsolution
 * @subpackage	front.src
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Some utility-methods for the online-list and other stuff
 *
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_OnlineUtils extends FWS_UtilBase
{
	/**
	 * Adds a block with the currently online users
	 * 
	 * @param string $loc the location: forums, topics, posts
	 */
	public static function add_currently_online($loc = 'forums')
	{
		$tpl = FWS_Props::get()->tpl();
		$auth = FWS_Props::get()->auth();
		$locale = FWS_Props::get()->locale();

		$online = self::get_currently_online_user($loc);
		
		$tpl->set_template('inc_online_user.htm');
		$tpl->add_variables(array(
			'can_view_locations' => $auth->has_global_permission('view_online_locations'),
			'conclusion_list' => $online['conclusion'],
			'title' => $locale->lang('currently_online_'.$loc),
			'user_list' => $online['online_reg'],
			'legend' => self::get_usergroup_legend()
		));
		$tpl->restore_template();
	}
	
	/**
	 * Gathers the information to display the currently online user
	 * 
	 * @param string $location the location: all, forums, topics, posts
	 * @return array an array of the form:
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
	public static function get_currently_online_user($location = 'forums')
	{
		$sessions = FWS_Props::get()->sessions();
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
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
				$users = $sessions->get_user_at_location($location);
				break;
			case 'topics':
				$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
				$users = $sessions->get_user_at_location($location,$fid);
				break;
			case 'posts':
				$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
				$users = $sessions->get_user_at_location($location,$tid);
				break;
		}
		
		$durl = BS_URL::get_mod_url('userdetails');
		
		// sort user by date descending
		usort($users,create_function('$a,$b','return $b[\'date\'] - $a[\'date\'];'));
		$permission_to_view_location = $auth->has_global_permission('view_online_locations');
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
			else if($user->is_admin() || $daten['ghost_mode'] == 0 ||
				$cfg['allow_ghost_mode'] == 0)
			{
				$location = '';
				if($permission_to_view_location)
				{
					$loc = new BS_Location($daten['location']);
					$location = $loc->decode();
					$location = strip_tags($location);
					$location = str_replace('"','&quot;',$location);
				}
	
				$time = strip_tags(FWS_Date::get_date($daten['date']));
				$murl = $durl->set(BS_URL_ID,$daten['user_id'])->to_url();
				$name = $auth->get_colored_username(
					$daten['user_id'],$daten['user_name'],$daten['user_group']
				);
				
				$useronline .= '<a title="'.$location.' ('.$time.')" href="'.$murl.'">';
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
			$user_names = FWS_String::substr($useronline,0,FWS_String::strlen($useronline) - 2);
		else
			$user_names = '<i>'.$locale->lang('none').'</i>';
		
		// build conclusion
		$conc = '<b>'.$registered.'</b> ';
		$conc .= ($registered == 1) ? $locale->lang('Registered1') : $locale->lang('Registered');
		if($cfg['allow_ghost_mode'] == 1)
		{
			$conc .= ', <b>'.$ghosts.'</b> ';
			$conc .= ($ghosts == 1) ? $locale->lang('hiddenuser1') : $locale->lang('hiddenuser');
		}
		$conc .= ', <b>'.$guests.'</b> ';
		$conc .= ($guests == 1) ? $locale->lang('guest') : $locale->lang('guests');
		$conc .= ', <b>'.$bots.'</b> ';
		$conc .= ($bots == 1) ? $locale->lang('bot') : $locale->lang('bots');
		
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
	public static function get_usergroup_legend()
	{
		$cache = FWS_Props::get()->cache();
		$auth = FWS_Props::get()->auth();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		$legend = '';
		$groups = $cache->get_cache('user_groups');
		
		$murl = BS_URL::get_mod_url('memberlist');
		foreach($groups as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST && $gdata['is_visible'] == 1)
			{
				$gname = $auth->get_colored_groupname($gdata['id']);
				if($cfg['enable_memberlist'] == 1)
				{
					$murl->set(BS_URL_MS_GROUP,array($gdata['id']));
					$legend .= '<a href="'.$murl->to_url().'">'.$gname.'</a>, ';
				}
				else
					$legend .= $gname.', ';
			}
		}
		
		// add moderator
		if($cfg['enable_moderators'])
		{
			$murl->remove(BS_URL_MS_GROUP);
			$murl->set(BS_URL_MS_MODS,1);
			$legend .= '<a href="'.$murl->to_url().'"><span style="color: #'.$cfg['mod_color'].';">';
			$legend .= $locale->lang('moderators').'</span></a>';
		}
		else
			$legend = FWS_String::substr($legend,0,FWS_String::strlen($legend) - 2);
		
		return $legend;
	}
	
	/**
	 * Builds the last-activity string
	 *
	 * @return string the string
	 */
	public static function get_last_activity()
	{
		$sessions = FWS_Props::get()->sessions();
		$locale = FWS_Props::get()->locale();

		$last = $sessions->get_last_login();
		if($last['id'] != '' && $last['lastlogin'] > 0)
		{
			$lastlogin = FWS_Date::get_date($last['lastlogin']).' '.$locale->lang('of').' ';
			$lastlogin .= BS_UserUtils::get_link($last['id'],$last['user_name'],$last['user_group']);
		}
		else
			$lastlogin = '';
		
		return $lastlogin;
	}
}
?>