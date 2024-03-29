<?php
/**
 * Contains the user-utils-class
 * 
 * @package			Boardsolution
 * @subpackage	src
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
 * Contains several methods for user. This class is realized as singleton.
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_UserUtils extends FWS_UtilBase
{
	/**
	 * Builds a link to the userdetails of the given user
	 * 
	 * @param int $id the user-id
	 * @param string $name the username
	 * @param string $group_ids the usergroup-ids of the user (if color should be used)
	 * @param boolean $color use the user-color?
	 * @param string $style additional style information
	 * @return string the link to the userdetails
	 */
	public static function get_link($id,$name,$group_ids = '',$color = false,$style = '')
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		static $user_cache = array();
		
		if($cfg['always_color_usernames'])
			$color = true;
		
		// do we have it already in the cache?
		if(isset($user_cache[$id.$color.$style]))
			return $user_cache[$id.$color.$style];
		
		$murl = BS_URL::get_mod_url('userdetails');
		$murl->set(BS_URL_ID,$id);
		$link = '<a';
		if($style != '')
			$link .= ' style="'.$style.'"';
		$link .= ' href="'.$murl->to_url().'">';
		
		if($color && $group_ids != '')
		{
			$c = $auth->get_user_color($id,$group_ids);
			$link .= '<span style="color: #'.$c.';">';
		}
		
		$link .= $name;
		
		if($color && $group_ids != '')
			$link .= '</span>';
		
		$link .= '</a>';
		
		// store link to cache
		$user_cache[$id.$color.$style] = $link;
		
		return $link;
	}
	
	/**
	 * checks if the given user has voted the given poll
	 *
	 * @param int $pollid the id of the poll
	 * @param int $userid the id of the user (0 = current)
	 * @return boolean true if the user has voted
	 */
	public static function user_voted_for_poll($pollid,$userid = 0)
	{
		$user = FWS_Props::get()->user();

		if($userid == 0)
		{
			$userid = $user->get_user_id();
			if($userid == 0)
				return false;
		}
		
		return BS_DAO::get_pollvotes()->user_voted($pollid,$userid);
	}
	
	/**
	 * Checks wether the current user has voted for the given link
	 *
	 * @param int $linkid the id of the link
	 * @return boolean true if the current user has voted for the link
	 */
	public static function user_voted_for_link($linkid)
	{
		$user = FWS_Props::get()->user();

		static $votes = null;
		if($votes === null)
		{
			$userid = $user->get_user_id();
			if($userid == 0)
				$votes = array();
			else
			{
				$votes = BS_DAO::get_linkvotes()->get_votes_of_user($userid);
				$votes = FWS_Array_Utils::get_fast_access($votes);
			}
		}
		
		return isset($votes[$linkid]);
	}
	
	/**
	 * returns the email to display depending on the given mode
	 *
	 * @param string $email the email-address
	 * @param string $mode the display-mode (hide,default,jumble)
	 * @param boolean $use_link do you want to use a link if possible?
	 * @return string the email to display
	 */
	public static function get_displayed_email($email,$mode,$use_link = false)
	{
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();

		// admins can always view the email-address
		if($user->is_admin())
			$mode = 'default';
		
		switch($mode)
		{
			case 'hide':
				return $locale->lang('notavailable');
	
			case 'default':
				if($use_link)
					return '<a href="mailto:'.$email.'">'.$email.'</a>';
				return $email;
	
			case 'jumble':
				$email = str_replace('@',' [at] ',$email);
				$email = preg_replace('/\.([a-z0-9]+)$/i',' [dot] \\1',$email);
				return $email;
		}
	
		return $email;
	}
	
	/**
	 * checks wether the user is allowed to use this avatar and returns the path if this is the case
	 *
	 * @param array $data an associative array which must contain the following values:
	 * 	<code>
	 * 		'av_pfad' => <avatar>,
	 * 		'aowner' => <idOfOwner>,
	 * 		'post_user' => <userid>
	 * 	</code>
	 * @return string the path to the given avatar if it's valid
	 */
	public static function get_avatar_path($data)
	{
		if($data['av_pfad'] != '' && ($data['aowner'] == $data['post_user'] || $data['aowner'] == 0))
			return FWS_Path::client_app().'images/avatars/'.$data['av_pfad'];
	
		return '';
	}
	
	/**
	 * returns the image-tag or an error for the avatar-display in the user-details.
	 * will also check if the user is allowed to use the avatar
	 *
	 * @param int $avatar_id the id of the avatar
	 * @param int $user_id the id of the user
	 * @return string the result
	 */
	public static function get_profile_avatar($avatar_id,$user_id)
	{
		$locale = FWS_Props::get()->locale();

		if($avatar_id > 0)
		{
			$avatar = BS_DAO::get_avatars()->get_by_id($avatar_id);
			if($avatar !== false && ($avatar['user'] == $user_id || $avatar['user'] == 0))
			{
				$image = FWS_Path::client_app().'images/avatars/'.$avatar['av_pfad'];
				return '<img src="'.$image.'" alt="" />';
			}
	
			return $locale->lang('nopictureavailable');
		}
	
		return $locale->lang('nopictureavailable');
	}
	
	/**
	 * checks wether the given username is valid
	 *
	 * @param string $user_name the user-name
	 * @return boolean true if it's valid
	 */
	public static function check_username($user_name)
	{
		$cfg = FWS_Props::get()->cfg();

		if($user_name && preg_match("/([\"\\/|'])/i",$user_name))
			return false;
	
		if($cfg['profile_user_special_chars'] == 0 &&
			(!$user_name || !preg_match('/^([a-z|0-9|_|\\-|\\[|\\]]+)$/i',$user_name)))
			return false;
	
		return true;
	}
}
?>