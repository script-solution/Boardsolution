<?php
/**
 * Contains the community-manager-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The community-manager of Boardsolution. You can register listeners to get notified of
 * logins, logouts, registrations and so on. Additionally you can disable features and change
 * links to some features (registration, send-pw, ...).
 * 
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Community_Manager extends FWS_Singleton
{
	/**
	 * @return BS_Community_Manager the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * The export-implementation
	 *
	 * @var BS_Community_Export
	 */
	private $_export = null;
	
	/**
	 * The login-implementation
	 *
	 * @var BS_Community_Login
	 */
	private $_login = null;
	
	/**
	 * Wether the user-management is enabled
	 *
	 * @var boolean
	 */
	private $_user_management_enabled = true;
	
	/**
	 * Wether the registration is enabled
	 *
	 * @var boolean
	 */
	private $_registration_enabled = true;
	
	/**
	 * Wether the resend-activation-feature is enabled
	 *
	 * @var boolean
	 */
	private $_resend_act_enabled = true;
	
	/**
	 * Wether the send-pw-feature is enabled
	 *
	 * @var boolean
	 */
	private $_send_pw_enabled = true;
	
	/**
	 * The URL for the registration
	 *
	 * @var string
	 */
	private $_register_url;
	
	/**
	 * The URL for the resend-activation-feature
	 *
	 * @var string
	 */
	private $_resend_act_url;
	
	/**
	 * The URL for the send-pw-feature
	 *
	 * @var string
	 */
	private $_send_pw_url;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		// build default URLs
		$this->_register_url = BS_URL::build_mod_url('register');
		$this->_send_pw_url = BS_URL::build_mod_url('sendpw');
		$this->_resend_act_url = BS_URL::build_mod_url('resend_activation');
	}

	/**
	 * @return boolean wether the user-management is enabled
	 */
	public function is_user_management_enabled()
	{
		return $this->_user_management_enabled;
	}

	/**
	 * Disables the user-management
	 */
	public function disable_user_management()
	{
		$this->_user_management_enabled = false;
	}

	/**
	 * @return boolean wether the registration is enabled
	 */
	public function is_registration_enabled()
	{
		return $this->_registration_enabled;
	}

	/**
	 * Disables the registration
	 */
	public function disable_registration()
	{
		$this->_registration_enabled = false;
	}

	/**
	 * @return boolean wether the resend-activation-feature is enabled
	 */
	public function is_resend_act_enabled()
	{
		return $this->_resend_act_enabled;
	}

	/**
	 * Disables the resend-activation-feature
	 */
	public function disable_resend_act()
	{
		$this->_resend_act_enabled = false;
	}

	/**
	 * @return boolean wether the send-pw-feature is enabled
	 */
	public function is_send_pw_enabled()
	{
		return $this->_send_pw_enabled;
	}

	/**
	 * Disables the send-pw-feature
	 */
	public function disable_send_pw()
	{
		$this->_send_pw_enabled = false;
	}

	/**
	 * @return string the URL for the registration (empty = don't display)
	 */
	public function get_register_url()
	{
		return $this->_register_url;
	}

	/**
	 * Sets the URL for the registration
	 * 
	 * @param string $url the new value (empty = don't display)
	 */
	public function set_register_url($url)
	{
		$this->_register_url = $url;
	}

	/**
	 * @return string the URL for the send-pw-feature (empty = don't display)
	 */
	public function get_send_pw_url()
	{
		return $this->_send_pw_url;
	}

	/**
	 * Sets the URL for the send-pw-feature
	 * 
	 * @param string $url the new value (empty = don't display)
	 */
	public function set_send_pw_url($url)
	{
		$this->_send_pw_url = $url;
	}

	/**
	 * @return string the URL for the resend-activation-feature (empty = don't display)
	 */
	public function get_resend_act_url()
	{
		return $this->_resend_act_url;
	}

	/**
	 * Sets the URL for the resend-activation-feature
	 * 
	 * @param string $url the new value (empty = don't display)
	 */
	public function set_resend_act_url($url)
	{
		$this->_resend_act_url = $url;
	}
	
	/**
	 * Registers the given login-listener.
	 *
	 * @param BS_Community_Login $login the login-listener
	 */
	public function add_login_listener($login)
	{
		if(!($login instanceof BS_Community_Login))
			FWS_Helper::def_error('instance','login','BS_Community_Login',$login);
		
		$this->_login = $login;
	}
	
	/**
	 * Registers the given export-listener.
	 *
	 * @param BS_Community_Export $export the export-listener
	 */
	public function add_export_listener($export)
	{
		if(!($export instanceof BS_Community_Export))
			FWS_Helper::def_error('instance','export','BS_Community_Export',$export);
		
		$this->_export = $export;
	}
	
	/**
	 * Fires the 'user-registered'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_registered($user)
	{
		if($this->_export !== null)
			$this->_export->user_registered($user);
	}
	
	/**
	 * Fires the 'login'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_login($user)
	{
		if($this->_login !== null)
			$this->_login->user_login($user);
	}
	
	/**
	 * Fires the 'logout'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_logout($user)
	{
		if($this->_login !== null)
			$this->_login->user_logout($user);
	}
	
	/**
	 * Fires the 'user-reactivated'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_reactivated($user)
	{
		if($this->_export !== null)
			$this->_export->user_reactivated($user);
	}
	
	/**
	 * Fires the 'user-deactivated'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_deactivated($user)
	{
		if($this->_export !== null)
			$this->_export->user_deactivated($user);
	}
	
	/**
	 * Fires the 'user-changed'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_changed($user)
	{
		if($this->_export !== null)
			$this->_export->user_changed($user);
	}
	
	/**
	 * Fires the 'user-deleted'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_deleted($user)
	{
		if($this->_export !== null)
			$this->_export->user_deleted($user);
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>