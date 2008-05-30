<?php
/**
 * Contains the edituser-submodule for moderators
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edituser sub-module for the moderators-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_moderators_edituser extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_CONFIG_MOD_FORUMS => 'edituser'
		);
	}
	
	public function run()
	{
		$usernames = $this->input->get_var('usernames','get',PLIB_Input::STRING);
		$auser = preg_split('/\s*,\s*/',$usernames);
		if(count($auser) == 0)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('username_not_found'));
			return;
		}
		
		// grab user from db
		$user_ids = array();
		foreach(BS_DAO::get_user()->get_users_by_names($auser) as $row)
			$user_ids[$row['id']] = $row['user_name'];
		
		// any user found?
		if(count($user_ids) == 0)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('username_not_found'));
			return;
		}
		
		// collect forums of the user
		$forums = array();
		foreach(BS_DAO::get_mods()->get_by_user_ids(array_keys($user_ids)) as $row)
		{
			if(!isset($forums[$row['user_id']]))
				$forums[$row['user_id']] = array();
			$forums[$row['user_id']][] = $row['rid'];
		}
		
		// build template-loop-data
		$user = array();
		foreach($user_ids as $id => $name)
		{
			$user[] = array(
				'id' => $id,
				'name' => $name,
				'forum_combo' => BS_ForumUtils::get_instance()->get_recursive_forum_combo(
					'forums['.$id.'][]',isset($forums[$id]) ? $forums[$id] : array(),0,true,false
				)
			);
		}
		
		$this->tpl->add_variables(array(
			'user' => $user,
			'action_type' => BS_ACP_ACTION_CONFIG_MOD_FORUMS,
			'usernames' => $usernames
		));
	}
	
	public function get_location()
	{
		$usernames = $this->input->get_var('usernames','get',PLIB_Input::STRING);
		return array(
			$this->locale->lang('config_mod_forums') =>
				$this->url->get_acpmod_url(0,'&amp;action=edituser&amp;usernames='.$usernames)
		);
	}
}
?>