<?php
/**
 * Contains the class which represents the current user
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.user
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Represents the current user. Contains a PLIB_Session_Data object and some
 * more information for the current user. It manages the login-state and some
 * other stuff.
 *
 * @package			Boardsolution
 * @subpackage	src.user
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_User_Current extends PLIB_User_Current
{
	/**
	 * Indicates that the user has reached the max. login tries
	 */
	const LOGIN_ERROR_MAX_LOGIN_TRIES			= 100;
	
	/**
	 * Indicates that the entered security-code is wrong (after the max. login-tries)
	 */
	const LOGIN_ERROR_INVALID_SEC_CODE		= 101;
	
	/**
	 * Indicates that the user is not activated
	 */
	const LOGIN_ERROR_NOT_ACTIVATED				= 102;
	
	/**
	 * Indicates that the user is banned
	 */
	const LOGIN_ERROR_BANNED							= 103;
	
	/**
	 * Indicates that the user is no admin, but an admin is required
	 */
	const LOGIN_ERROR_ADMIN_REQUIRED			= 104;
	
	/**
	 * Indicates that the user is a bot (bots are not allowed to login)
	 */
	const LOGIN_ERROR_BOT									= 105;
	
	/**
	 * Indicates that the IP is invalid (e.g. empty)
	 */
	const LOGIN_ERROR_INVALID_IP					= 106;
	
	/**
	 * The name of the language-folder that is used
	 *
	 * @var string
	 */
	private $_language = '';
	
	/**
	 * Indicates wether the login should be stored to the ip-log
	 *
	 * @var boolean
	 */
	private $_store_login = false;
	
	/**
	 * Stores wether the max-login tries have been reached
	 *
	 * @var boolean
	 */
	private $_max_login_tries = false;
	
	public function init()
	{
		parent::init();
		
		$this->_determine_language();
		$this->_determine_theme();
		
		// store the location
		if(!$this->doc->is_standalone())
			$this->_user->set_location(BS_Location::get_instance()->get_location());
	}

	public function finalize()
	{
		parent::finalize();
		
		if($this->_store_login)
			$this->ips->add_entry('login');
	}
	
	/**
	 * @return boolean wether the max-login-tries have been reached
	 */
	public function has_reached_max_login_tries()
	{
		return $this->_max_login_tries;
	}
	
	/**
	 * Override this method to add support for a limited number of login-tries
	 *
	 * @param string $user the entered user-name
	 * @param string $pw the entered password
	 * @param boolean $hash does the password need to be hashed?
	 * @return int the error-code; see BS_LOGIN_ERROR_*
	 */
	public function login($user,$pw,$hashpw = true)
	{
		$loggedin = $this->_set_userdata(0,$user);
		
		// login confirmation?
		if($this->input->isset_var('login','post') && $this->cfg['enable_security_code'] == 1)
		{
	    if($this->input->isset_var('conf','post'))
			{
				$hashpw = false; // the password is already hashed
				if(!$this->functions->check_security_code(false))
					$loggedin = self::LOGIN_ERROR_INVALID_SEC_CODE;
			}
			// max login tries?
			else if($this->cfg['profile_max_login_tries'] > 0 && $this->_userdata !== null &&
							$this->_userdata->get_profile_val('login_tries') >= $this->cfg['profile_max_login_tries'])
	    {
	    	$this->_max_login_tries = true;
				$loggedin = self::LOGIN_ERROR_MAX_LOGIN_TRIES; // she/he has to confirm the login
	  	}
		}
		
		if($hashpw && $loggedin == self::LOGIN_ERROR_NO_ERROR)
			$pw = $this->_storage->get_hash_of_pw($pw,$this->_userdata);
		
		if($loggedin == self::LOGIN_ERROR_NO_ERROR)
		{
	    // perform stripslashes here because addslashes() has been called on the value
		  // and we want to compare it with as it is
			$user = stripslashes($user);
	
			if(empty($pw))
		    $loggedin = self::LOGIN_ERROR_PW_INCORRECT;
		  else
		  	$loggedin = $this->_check_user($user,$pw);
		}
		
		// increase the login-tries, if the pw was incorrect
		if($loggedin == self::LOGIN_ERROR_PW_INCORRECT)
		{
			// increase login tries
			if($this->cfg['profile_max_login_tries'] > 0 &&
				 $this->_userdata->get_profile_val('login_tries') < $this->cfg['profile_max_login_tries'])
			{
				BS_DAO::get_profile()->update_user_by_id(
					array('login_tries' => array('login_tries + 1')),$this->user->get_user_id()
				);
			}
		}
		
		// setup user or guest
		if($loggedin == self::LOGIN_ERROR_NO_ERROR)
		{
			// reset login-tries
			if($this->cfg['profile_max_login_tries'] > 0 &&
				$this->_userdata->get_profile_val('login_tries') > 0)
			{
				BS_DAO::get_profile()->update_user_by_id(
					array('login_tries' => 0),$this->user->get_user_id()
				);
			}
			
			// we want to log this login
			$this->_store_login = true;
			// store lastlogin
			$this->cookies->set_cookie('lastlogin',$this->_userdata->get_profile_val('lastlogin'));
			
			$this->_setup_user($user,$pw);
		}
		else
			$this->_setup_guest();
		
		$this->_refresh_stats($loggedin == self::LOGIN_ERROR_NO_ERROR);

		return $loggedin;
	}

	/**
	 * @return boolean wether we want to force an unread-update
	 */
	public function force_unread_update()
	{
		return $this->_storage->force_unread_update();
	}
	
	/**
	 * @return string the name of the language-folder that is used
	 */
	public function get_language()
	{
		return $this->_language;
	}
	
	public function get_theme_item_path($item)
	{
		$base = PLIB_Path::inner().'themes/';
		
		// at first we look in the selected theme
		$path = $base.$this->_theme.'/'.$item;
		if(is_file($path))
			return $path;
		
		// if the file does not exist, we use the default theme
		return $base.'default/'.$item;
	}
	
	/**
	 * Returns the value of the profile-table-field with given name
	 *
	 * @param string $name the field-name
	 * @return string the value
	 */
	public function get_profile_val($name)
	{
		if($this->_userdata === null)
			return null;
		
		return $this->_userdata->get_profile_val($name);
	}
	
	/**
	 * Sets the value of the profile-table-field with given name to given value
	 *
	 * @param string $name the field-name
	 * @param string $value the new value
	 */
	public function set_profile_val($name,$value)
	{
		if($this->_userdata === null)
			return;
		
		$this->_userdata->set_profile_val($name,$value);
	}
	
	/**
	 * @return boolean true if the user is a bot
	 */
	public function is_bot()
	{
		return $this->_user->is_bot();
	}

	/**
	 * @return boolean true if this user is an admin
	 */
	public function is_admin()
	{
		return $this->get_user_id() > 0 && in_array(BS_STATUS_ADMIN,$this->get_all_user_groups());
	}

	/**
	 * @return int the main-user-group of this user
	 */
	public function get_user_group()
	{
		if(!$this->is_loggedin())
			return BS_STATUS_GUEST;

		return (int)$this->get_profile_val('user_group');
	}

	/**
	 * @return array an array with all user-group-ids
	 */
	public function get_all_user_groups()
	{
		if(!$this->is_loggedin())
			return array(BS_STATUS_GUEST);

		static $ugarray = null;
		if($ugarray == null)
			$ugarray = PLIB_Array_Utils::advanced_explode(',',$this->get_profile_val('user_group'));

		return $ugarray;
	}
	
	/**
	 * determines wether this user wants to use the bbcode-applet
	 * 
	 * @return boolean true if so
	 */
	public function use_bbcode_applet()
	{
		// not enabled?
		if(!$this->cfg['msgs_allow_java_applet'])
			return false;
		
		if($this->is_loggedin())
			return $this->get_profile_val('bbcode_mode') == 'applet';
		
		return $this->cfg['msgs_default_bbcode_mode'] == 'applet';
	}
	
	/**
	 * Refreshes the statistics
	 *
	 * @param boolean $refresh_logins do you want to refresh the logins, too?
	 */
	private function _refresh_stats($refresh_logins)
	{
		$stats_data = $this->cache->get_cache('stats')->current();
		$time = time();
		$regen_stats = false;

		// calculate the values for the stats
		if($refresh_logins)
		{
			$yd = new PLIB_Date();
			$yd->modify('-1day');
			$yesterday = $yd->to_format('dmY');
			
			BS_DAO::get_profile()->update_user_by_id(
				array('logins' => array('logins + 1'),'lastlogin' => time()),$this->user->get_user_id()
			);

			$lastlogin = PLIB_Date::get_formated_date('dmY',$stats_data['logins_last']);

			if($lastlogin == PLIB_Date::get_formated_date('dmY'))
			{
				$this->cache->get_cache('stats')->set_element_field(
					0,'logins_today',$stats_data['logins_today'] + 1
				);
			}
			else if($lastlogin == $yesterday)
			{
				$this->cache->get_cache('stats')->set_element_field(
					0,'logins_yesterday',$stats_data['logins_today']
				);
				$this->cache->get_cache('stats')->set_element_field(0,'logins_today',1);
			}
			else if($lastlogin < $yesterday)
			{
				$this->cache->get_cache('stats')->set_element_field(0,'logins_yesterday',0);
				$this->cache->get_cache('stats')->set_element_field(0,'logins_today',1);
			}

			$this->cache->get_cache('stats')->set_element_field(
				0,'logins_total',$stats_data['logins_total'] + 1
			);
			$this->cache->get_cache('stats')->set_element_field(0,'logins_last',$time);

			$regen_stats = true;
		}

		// refresh max-online?
		$online_num = $this->sessions->get_online_count();
		if($online_num > $stats_data['max_online'])
		{
			$this->cache->get_cache('stats')->set_element_field(0,'max_online',$online_num);
			$regen_stats = true;
		}

		if($regen_stats)
			$this->cache->store('stats');
	}

	/**
	 * Calculates the language
	 */
	private function _determine_language()
	{
		if($this->is_loggedin() && $this->cfg['allow_custom_lang'] == 1)
		{
			if($this->get_profile_val('forum_lang') > 0)
			{
				$this->_language = $this->get_profile_val('lang_folder');
				return;
			}
		}

		if($this->cfg['default_forum_lang'] > 0)
		{
			$data = $this->cache->get_cache('languages')->get_element($this->cfg['default_forum_lang']);
			$this->_language = $data['lang_folder'];
		}
	}

	/**
	 * Determines the theme that should be used
	 */
	private function _determine_theme()
	{
		// is it a mobile device?
		if($this->_user->uses_mobile_device())
		{
			$this->set_theme('bots');
			return;
		}
		
		// use theme of user?
		if($this->is_loggedin() && $this->cfg['allow_custom_style'] == 1)
		{
			if($this->get_profile_val('forum_style') > 0)
			{
				$this->set_theme($this->get_profile_val('theme_folder'));
				return;
			}
		}

		if($this->cfg['default_forum_style'] > 0)
		{
			$data = $this->cache->get_cache('themes')->get_element($this->cfg['default_forum_style']);
			$this->set_theme($data['theme_folder']);
		}
	}
	
	protected function _get_print_vars()
	{
		return array_merge(parent::_get_print_vars(),get_object_vars($this));
	}
}
?>