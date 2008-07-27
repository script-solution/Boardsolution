<?php
/**
 * Contains the user-utils-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Contains several methods for user. This class is realized as singleton.
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_UserUtils extends PLIB_Singleton
{
	/**
	 * @return BS_UserUtils the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
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
	public function get_link($id,$name,$group_ids = '',$color = false,$style = '')
	{
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		$url = PLIB_Props::get()->url();

		static $user_cache = array();
		
		if($cfg['always_color_usernames'])
			$color = true;
		
		// do we have it already in the cache?
		if(isset($user_cache[$id.$color.$style]))
			return $user_cache[$id.$color.$style];
		
		$murl = $url->get_url('userdetails','&amp;'.BS_URL_ID.'='.$id);
		$link = '<a';
		if($style != '')
			$link .= ' style="'.$style.'"';
		$link .= ' href="'.$murl.'">';
		
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
	public function user_voted_for_poll($pollid,$userid = 0)
	{
		$user = PLIB_Props::get()->user();

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
	public function user_voted_for_link($linkid)
	{
		$user = PLIB_Props::get()->user();

		static $votes = null;
		if($votes === null)
		{
			$userid = $user->get_user_id();
			if($userid == 0)
				$votes = array();
			else
			{
				$votes = BS_DAO::get_linkvotes()->get_votes_of_user($userid);
				$votes = PLIB_Array_Utils::get_fast_access($votes);
			}
		}
		
		return isset($votes[$linkid]);
	}
	
	/**
	 * returns the email to display depending on the given mode
	 *
	 * @param string $email the email-address
	 * @param string $mode the display-mode (hide,default,jumble)
	 * @param string $use_link do you want to use a link if possible?
	 * @return string the email to display
	 */
	public function get_displayed_email($email,$mode,$use_link = false)
	{
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();

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
	public function get_avatar_path($data)
	{
		if($data['av_pfad'] != '' && ($data['aowner'] == $data['post_user'] || $data['aowner'] == 0))
			return PLIB_Path::client_app().'images/avatars/'.$data['av_pfad'];
	
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
	public function get_profile_avatar($avatar_id,$user_id)
	{
		$locale = PLIB_Props::get()->locale();

		if($avatar_id > 0)
		{
			$avatar = BS_DAO::get_avatars()->get_by_id($avatar_id);
			if($avatar !== false && ($avatar['user'] == $user_id || $avatar['user'] == 0))
			{
				$image = PLIB_Path::client_app().'images/avatars/'.$avatar['av_pfad'];
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
	public function check_username($user_name)
	{
		$cfg = PLIB_Props::get()->cfg();

		if(preg_match("/([\"\\/|'])/i",$user_name))
			return false;
	
		if($cfg['profile_user_special_chars'] == 0 &&
			!preg_match('/^([a-z|0-9|_|\\-|\\[|\\]]+)$/i',$user_name))
			return false;
	
		return true;
	}
}
?>