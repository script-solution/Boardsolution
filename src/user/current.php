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
 * Represents the current user. Contains a FWS_Session_Data object and some
 * more information for the current user. It manages the login-state and some
 * other stuff.
 *
 * @package			Boardsolution
 * @subpackage	src.user
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_User_Current extends FWS_User_Current
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

	/**
	 * @see FWS_User_Current::init()
	 */
	public function init()
	{
		$input = FWS_Props::get()->input();
		$cookies = FWS_Props::get()->cookies();

		// we disable cookies if the user wants to get logged out. because if the session
		// doesn't exist anymore we assign a new sid, login the user again and the logout fails
		// because of a wrong session-id
		if($input->get_var(BS_URL_AT,'get',FWS_Input::INTEGER) === BS_ACTION_LOGOUT)
		{
			// delete the cookies, too
			$cookies->delete_cookie('user');
	    $cookies->delete_cookie('pw');
			$this->set_use_cookies(false);
		}
		
		parent::init();
		
		$this->_determine_language();
		$this->_determine_theme();
	}

	/**
	 * @see FWS_User_Current::finalize()
	 */
	public function finalize()
	{
		$ips = FWS_Props::get()->ips();

		parent::finalize();
		
		if($this->_store_login)
			$ips->add_entry('login');
	}
	
	/**
	 * Sets the current location of the user
	 */
	public function set_location()
	{
		$this->_user->set_location(BS_Location::get_instance()->get_location());
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
	 * @param string $username the entered user-name
	 * @param string $pw the entered password
	 * @param boolean $hashpw does the password need to be hashed?
	 * @return int the error-code; see self::LOGIN_ERROR_*
	 */
	public function login($username,$pw,$hashpw = true)
	{
		$input = FWS_Props::get()->input();
		$cfg = FWS_Props::get()->cfg();
		$functions = FWS_Props::get()->functions();
		$cookies = FWS_Props::get()->cookies();

		$oldpw = $pw;
		$loggedin = $this->set_userdata(0,$username);
		
		// login confirmation?
		if($input->isset_var('login','post') && $cfg['enable_security_code'] == 1)
		{
	    if($input->isset_var('conf','post'))
			{
				$hashpw = false; // the password is already hashed
				if(!$functions->check_security_code(false))
					$loggedin = self::LOGIN_ERROR_INVALID_SEC_CODE;
			}
			// max login tries?
			else if($cfg['profile_max_login_tries'] > 0 && $this->_userdata !== null &&
							$this->_userdata->get_profile_val('login_tries') >= $cfg['profile_max_login_tries'])
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
			$username = stripslashes($username);
	
			if(empty($pw))
		    $loggedin = self::LOGIN_ERROR_PW_INCORRECT;
		  else
		  	$loggedin = $this->check_user($username,$pw);
		}
		
		// increase the login-tries, if the pw was incorrect
		if($loggedin == self::LOGIN_ERROR_PW_INCORRECT)
		{
			// increase login tries
			if($cfg['profile_max_login_tries'] > 0 &&
				 $this->_userdata->get_profile_val('login_tries') < $cfg['profile_max_login_tries'])
			{
				BS_DAO::get_profile()->update_user_by_id(
					array('login_tries' => array('login_tries + 1')),$this->get_user_id()
				);
			}
		}
		
		// setup user or guest
		if($loggedin == self::LOGIN_ERROR_NO_ERROR)
		{
			// reset login-tries
			if($cfg['profile_max_login_tries'] > 0 &&
				$this->_userdata->get_profile_val('login_tries') > 0)
			{
				BS_DAO::get_profile()->update_user_by_id(
					array('login_tries' => 0),$this->get_user_id()
				);
			}
			
			// we want to log this login
			$this->_store_login = true;
			// store lastlogin
			$cookies->set_cookie('lastlogin',$this->_userdata->get_profile_val('lastlogin'));
			
			$this->setup_user($username,$pw);
			
			// fire community-event
			$status = BS_Community_User::get_status_from_groups($this->get_all_user_groups());
			$u = new BS_Community_User(
				$this->get_user_id(),$this->get_user_name(),
				$this->get_profile_val('user_email'),$status,$pw,$hashpw ? $oldpw : null
			);
			BS_Community_Manager::get_instance()->fire_user_login($u);
		}
		else
			$this->setup_guest();
		
		$this->_refresh_stats($loggedin == self::LOGIN_ERROR_NO_ERROR);

		return $loggedin;
	}

	/**
	 * @see FWS_User_Current::logout()
	 */
	public function logout()
	{
		$user = FWS_Props::get()->user();
		
		// collect user-data
		$status = BS_Community_User::get_status_from_groups($user->get_all_user_groups());
		$u = new BS_Community_User(
			$user->get_user_id(),$user->get_user_name(),
			$user->get_profile_val('user_email'),$status,$user->get_profile_val('user_pw')
		);
		
		// now logout
		parent::logout();
		
		// fire community-event
		BS_Community_Manager::get_instance()->fire_user_logout($u);
	}

	/**
	 * @see FWS_User_Current::set_userdata()
	 *
	 * @param int $id
	 * @param string $user
	 * @return int
	 */
	protected function set_userdata($id,$user = false)
	{
		$res = parent::set_userdata($id,$user);
		
		if($res == self::LOGIN_ERROR_NO_ERROR)
		{
			$this->_user->set_user_group($this->_userdata->get_profile_val('user_group'));
			$this->_user->set_ghost_mode($this->_userdata->get_profile_val('ghost_mode'));
		}
		
		return $res;
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
		// at first we look in the selected theme
		$path = 'themes/'.$this->_theme.'/'.$item;
		if(is_file(FWS_Path::server_app().$path))
			return FWS_Path::client_app().$path;
		
		// if the file does not exist, we use the default theme
		return FWS_Path::client_app().'themes/default/'.$item;
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
	 * @param int|bool|float|string $value the new value
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
	 * @return string the name of the bot. null if the user is no bot
	 */
	public function get_bot_name()
	{
		return $this->_user->get_bot_name();
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
			$ugarray = FWS_Array_Utils::advanced_explode(',',$this->get_profile_val('user_group'));

		return $ugarray;
	}
	
	/**
	 * determines wether this user wants to use the bbcode-applet
	 * 
	 * @return boolean true if so
	 */
	public function use_bbcode_applet()
	{
		$cfg = FWS_Props::get()->cfg();

		// not enabled?
		if(!$cfg['msgs_allow_java_applet'])
			return false;
		
		if($this->is_loggedin())
			return $this->get_profile_val('bbcode_mode') == 'applet';
		
		return $cfg['msgs_default_bbcode_mode'] == 'applet';
	}
	
	/**
	 * Refreshes the statistics
	 *
	 * @param boolean $refresh_logins do you want to refresh the logins, too?
	 */
	private function _refresh_stats($refresh_logins)
	{
		$cache = FWS_Props::get()->cache();
		$user = FWS_Props::get()->user();
		$sessions = FWS_Props::get()->sessions();

		$stats_data = $cache->get_cache('stats')->current();
		$time = time();
		$regen_stats = false;

		// calculate the values for the stats
		if($refresh_logins)
		{
			$yd = new FWS_Date();
			$yd->modify('-1day');
			$yesterday = $yd->to_format('dmY');
			
			BS_DAO::get_profile()->update_user_by_id(
				array('logins' => array('logins + 1'),'lastlogin' => time()),$user->get_user_id()
			);

			$lastlogin = FWS_Date::get_formated_date('dmY',$stats_data['logins_last']);

			if($lastlogin == FWS_Date::get_formated_date('dmY'))
			{
				$cache->get_cache('stats')->set_element_field(
					0,'logins_today',$stats_data['logins_today'] + 1
				);
			}
			else if($lastlogin == $yesterday)
			{
				$cache->get_cache('stats')->set_element_field(
					0,'logins_yesterday',$stats_data['logins_today']
				);
				$cache->get_cache('stats')->set_element_field(0,'logins_today',1);
			}
			else if($lastlogin < $yesterday)
			{
				$cache->get_cache('stats')->set_element_field(0,'logins_yesterday',0);
				$cache->get_cache('stats')->set_element_field(0,'logins_today',1);
			}

			$cache->get_cache('stats')->set_element_field(
				0,'logins_total',$stats_data['logins_total'] + 1
			);
			$cache->get_cache('stats')->set_element_field(0,'logins_last',$time);

			$regen_stats = true;
		}

		// refresh max-online?
		$online_num = $sessions->get_online_count();
		if($online_num > $stats_data['max_online'])
		{
			$cache->get_cache('stats')->set_element_field(0,'max_online',$online_num);
			$regen_stats = true;
		}

		if($regen_stats)
			$cache->store('stats');
	}

	/**
	 * Calculates the language
	 */
	private function _determine_language()
	{
		$cfg = FWS_Props::get()->cfg();
		$cache = FWS_Props::get()->cache();
		$functions = FWS_Props::get()->functions();

		if($this->is_loggedin() && $cfg['allow_custom_lang'] == 1)
		{
			if($this->get_profile_val('forum_lang') > 0)
			{
				$lang = $this->get_profile_val('forum_lang');
				$lang_data = $cache->get_cache('languages')->get_element($lang);
				$this->_language = $lang_data['lang_folder'];
				return;
			}
		}

		if($cfg['default_forum_lang'] > 0)
			$this->_language = $functions->get_def_lang_folder();
	}

	/**
	 * Determines the theme that should be used
	 */
	private function _determine_theme()
	{
		$cfg = FWS_Props::get()->cfg();
		$cache = FWS_Props::get()->cache();

		// is it a mobile device?
		if($this->_user->uses_mobile_device())
		{
			$this->set_theme('bots');
			return;
		}
		
		// use theme of user?
		if($this->is_loggedin() && $cfg['allow_custom_style'] == 1)
		{
			if($this->get_profile_val('forum_style') > 0)
			{
				$theme = $this->get_profile_val('forum_style');
				$theme_data = $cache->get_cache('themes')->get_element($theme);
				// just do this if it is a valid theme (if the cache does not exist for example)
				if(is_string($theme_data['theme_folder']))
					$this->set_theme($theme_data['theme_folder']);
				return;
			}
		}

		if($cfg['default_forum_style'] > 0)
		{
			$data = $cache->get_cache('themes')->get_element($cfg['default_forum_style']);
			// just do this if it is a valid theme (if the cache does not exist for example)
			if(is_string($data['theme_folder']))
				$this->set_theme($data['theme_folder']);
		}
	}
	
	protected function get_dump_vars()
	{
		return array_merge(parent::get_dump_vars(),get_object_vars($this));
	}
}
?>