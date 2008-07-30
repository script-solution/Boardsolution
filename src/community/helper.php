<?php
/**
 * Contains the community-helper-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Some helper-methods for the community
 *
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Community_Helper extends FWS_UtilBase
{
	/**
	 * Login's the given user. Actually the user will not be logged in but the cookies will be
	 * set so that she/he will be loggedin by them the next time she/he opens the board.
	 *
	 * @param string $name the user-name
	 * @param string $pw the md5-hash of the password
	 */
	public static function login($name,$pw)
	{
		$cookies = FWS_Props::get()->cookies();
		
		$cookies->set_cookie('user',$name);
		$cookies->set_cookie('pw',$pw);
	}
	
	/**
	 * Logouts the given user. More detailed: All user with the given user-id will be logged out.
	 *
	 * @param int $id the user-id
	 */
	public static function logout($id)
	{
		$sessions = FWS_Props::get()->sessions();
		$cookies = FWS_Props::get()->cookies();
		
		/* @var sessions BS_Session_Manager */
		foreach($sessions->get_online_list() as $data)
		{
			/* @var $data BS_Session_Data */
			if($data->get_user_id() == $id)
				$sessions->logout_user($data->get_session_id(),$data->get_user_ip());
		}
		
		$cookies->delete_cookie('user');
		$cookies->delete_cookie('pw');
	}
}
?>