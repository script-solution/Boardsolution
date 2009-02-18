<?php
/**
 * Contains the delete-user-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-user-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_user_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$com = BS_Community_Manager::get_instance();

		if(!$com->is_user_management_enabled())
			return 'The user-management is disabled';
		
		$idstr = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';
		
		$userdatas = array();
		
		// at first e collect all existing users and update their topics and posts so that they
		// have been created by guests (with the corresponding name)
		$existing_ids = array();
		foreach(BS_DAO::get_profile()->get_users_by_ids($ids) as $data)
		{
			if($data['id'] == $user->get_user_id())
				continue;

			$user_name = addslashes($data['user_name']);

			BS_DAO::get_posts()->assign_posts_to_guest($data['id'],$user_name);
			BS_DAO::get_topics()->assign_topics_to_guest($data['id'],$user_name);

			$existing_ids[] = $data['id'];
			$userdatas[] = $data;
		}

		// do we have any existing user?
		$count = count($existing_ids);
		if($count == 0)
			return 'No valid users found (do you want to delete yourself? ;))';
		
		BS_DAO::get_eventann()->delete_by_users($existing_ids);
		BS_DAO::get_acpaccess()->delete('user',$existing_ids);
		// delete just their attachments in pms
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
		
		// fire community-event
		foreach($userdatas as $data)
		{
			$u = BS_Community_User::get_instance_from_data($data);
			BS_Community_Manager::get_instance()->fire_user_deleted($u);
		}
		
		// finish
		$this->set_success_msg($locale->lang('user_deleted_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>