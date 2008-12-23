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
		if(!FWS_Helper::is_integer($id) || $id <= 0)
			FWS_Helper::def_error('intgt0','id',$id);
		
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
	
	/**
	 * Registers the given user.
	 * Note that the plain password is required!
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public static function register($user)
	{
		if(!($user instanceof BS_Community_User))
			FWS_Helper::def_error('instance','user','BS_Community_User',$user);
		
		// build default-values for the required fields
		$addfields = array();
		$afm = BS_AddField_Manager::get_instance();
		foreach($afm->get_required_fields() as $field)
			$addfields[$field->get_data()->get_name()] = $field->get_default_value();
		
		$plain = new BS_Front_Action_Plain_Register(
			$user->get_name(),$user->get_pw_plain(),$user->get_email(),array($user->get_status()),
			$user->get_id(),$addfields
		);
		if(($err = $plain->check_data()) != '')
			return;
		
		$plain->perform_action();
	}
	
	/**
	 * Changes the data of the given user (the id can't change!). That means the method ensures
	 * that the data in BS is equivalent to the data in your system. For example if the user
	 * was an admin before the admin-group will be removed from the groups of the user.
	 * Note that the plain password is required!
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public static function change($user)
	{
		// update user-data
		BS_DAO::get_user()->update(
			$user->get_id(),$user->get_name(),$user->get_pw_plain(),$user->get_email()
		);
		
		// get groups
		$profile = BS_DAO::get_profile()->get_user_by_id($user->get_id());
		$gids = FWS_Array_Utils::advanced_explode(',',$profile['user_group']);
		// build new groups
		$ngids = array();
		foreach($gids as $gid)
		{
			if($gid != BS_STATUS_ADMIN || $user->get_status() == BS_STATUS_ADMIN)
				$ngids[] = $gid;
		}
		if($user->get_status() == BS_STATUS_ADMIN && !in_array(BS_STATUS_ADMIN,$ngids))
			$ngids[] = BS_STATUS_ADMIN;

		// update groups
		BS_DAO::get_profile()->update_user_by_id(array(
			'user_group' => implode(',',$ngids).','
		),$user->get_id());
	}
	
	/**
	 * Deletes the users with given ids. Note that all posts and topics of the users will be
	 * "given" to guests and that all other data will be removed!
	 *
	 * @param array $ids the user-ids
	 */
	public static function delete($ids)
	{
		if(!is_array($ids) || !FWS_Array_Utils::is_integer($ids))
			FWS_Helper::def_error('intarray','ids',$ids);
		
		$cache = FWS_Props::get()->cache();
		
		// at first e collect all existing users and update their topics and posts so that they
		// have been created by guests (with the corresponding name)
		$existing_ids = array();
		foreach(BS_DAO::get_user()->get_users_by_ids($ids) as $data)
		{
			$user_name = addslashes($data['user_name']);

			BS_DAO::get_posts()->assign_posts_to_guest($data['id'],$user_name);
			BS_DAO::get_topics()->assign_topics_to_guest($data['id'],$user_name);

			$existing_ids[] = $data['id'];
		}

		// do we have any existing user?
		if(count($existing_ids) == 0)
			return;
		
		BS_DAO::get_eventann()->delete_by_users($existing_ids);
		BS_DAO::get_acpaccess()->delete('user',$existing_ids);
		BS_DAO::get_attachments()->delete_pm_attachments_of_users($existing_ids);
		BS_DAO::get_avatars()->delete_by_users($existing_ids);
		BS_DAO::get_links()->delete_by_users($existing_ids);
		BS_DAO::get_mods()->delete_by_users($existing_ids);
		BS_DAO::get_pms()->delete_by_user_ids($existing_ids);
		BS_DAO::get_sessions()->delete_by_users($existing_ids);
		BS_DAO::get_intern()->delete_by_users($existing_ids);
		BS_DAO::get_userbans()->delete_by_users($existing_ids);
		BS_DAO::get_unreadhide()->delete_by_users($existing_ids);
		BS_DAO::get_user()->delete($existing_ids);
		BS_DAO::get_profile()->delete($existing_ids);
		
		$cache->refresh('moderators');
		$cache->refresh('intern');
		$cache->refresh('acp_access');
	}
}
?>