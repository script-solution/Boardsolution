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
		if(BS_ENABLE_EXPORT)
			return 'The community is exported';
	
		$idstr = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($idstr)))
			return 'Got an invalid id-string via GET';
		
		// at first e collect all existing users and update their topics and posts so that they
		// have been created by guests (with the corresponding name)
		$existing_ids = array();
		foreach(BS_DAO::get_user()->get_users_by_ids($ids) as $data)
		{
			if($data['id'] == $this->user->get_user_id())
				continue;

			$user_name = addslashes($data['user_name']);

			BS_DAO::get_posts()->assign_posts_to_guest($data['id'],$user_name);
			BS_DAO::get_topics()->assign_topics_to_guest($data['id'],$user_name);

			$existing_ids[] = $data['id'];
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
		
		$this->cache->refresh('moderators');
		$this->cache->refresh('intern');
		$this->cache->refresh('acp_access');
		
		
		// finish
		$this->set_success_msg($this->locale->lang('user_deleted_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>