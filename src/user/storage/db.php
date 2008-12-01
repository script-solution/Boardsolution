<?php
/**
 * Contains the user-storage-db-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.user
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The class that implements the user-storage
 * 
 * @package			Boardsolution
 * @subpackage	src.user
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_User_Storage_DB extends FWS_Object implements FWS_User_Storage
{
	/**
	 * Stores wether we want to force an unread-update
	 *
	 * @var boolean
	 */
	private $_force_unread_update = false;
	
	/**
	 * Wether we are currently in the ACP
	 *
	 * @var boolean
	 */
	private $_is_acp;
	
	/**
	 * Constructor
	 *
	 * @param boolean $is_acp are we in the ACP?
	 */
	public function __construct($is_acp = false)
	{
		parent::__construct();
		$this->_is_acp = $is_acp;
	}
	
	/**
	 * @return boolean wether we want to force an unread-update
	 */
	public function force_unread_update()
	{
		return $this->_force_unread_update;
	}
	
	public function get_userdata_by_id($id)
	{
		$data = BS_DAO::get_profile()->get_user_by_id($id);
		if($data === false)
			return null;
		return new BS_User_Data($data);
	}
	
	public function get_userdata_by_name($name)
	{
		$data = BS_DAO::get_profile()->get_user_by_name($name);
		if($data === false)
			return null;
		return new BS_User_Data($data);
	}
	
	public function get_hash_of_pw($pw,$data)
	{
		return BS_Ex_get_stored_password($pw,$data->get_all_fields());
	}
	
	/**
	 * This method gives you the opportunity to perform additional checks. For example if
	 * the user is activated.
	 * 
	 * @param FWS_User_Data $userdata the data of the user
	 * @return int the error-code or FWS_User_Current::LOGIN_ERROR_NO_ERROR
	 */
	public function check_user($userdata)
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();

		if($userdata->get_profile_val('active') == 0 && $cfg['account_activation'] == 1)
			return BS_User_Current::LOGIN_ERROR_NOT_ACTIVATED;
		if($userdata->get_profile_val('banned') == 1)
			return BS_User_Current::LOGIN_ERROR_BANNED;
		if($this->_is_acp && !$auth->has_acp_access())
			return BS_User_Current::LOGIN_ERROR_ADMIN_REQUIRED;
		if($user->is_bot())
			return BS_User_Current::LOGIN_ERROR_BOT;
		
		return FWS_User_Current::LOGIN_ERROR_NO_ERROR;
	}
	
	/**
	 * Logins the user with given id. You may perform some actions if this happens.
	 * 
	 * @param int $id the id of the user
	 */
	public function login($id)
	{
		$cookies = FWS_Props::get()->cookies();
		$user = FWS_Props::get()->user();

		$this->_force_unread_update = true;

		$cookies->set_cookie('user',$user->get_user_name());
		$cookies->set_cookie('pw',$user->get_userdata()->get_user_pw());
	}
	
	/**
	 * Logouts the user with given id. You may perform some actions if this happens.
	 * 
	 * @param int $id the id of the user
	 */
	public function logout($id)
	{
		$cookies = FWS_Props::get()->cookies();

		$cookies->delete_cookie('user');
		$cookies->delete_cookie('pw');
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>