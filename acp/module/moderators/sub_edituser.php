<?php
/**
 * Contains the edituser-submodule for moderators
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_CONFIG_MOD_FORUMS,'edituser');
		
		$usernames = $input->get_var('usernames','get',FWS_Input::STRING);
		$url = BS_URL::get_acpsub_url();
		$url->set('usernames',$usernames);
		$renderer->add_breadcrumb($locale->lang('config_mod_forums'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		$usernames = $input->get_var('usernames','get',FWS_Input::STRING);
		$auser = preg_split('/\s*,\s*/',$usernames);
		if(count($auser) == 0)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('username_not_found'));
			return;
		}
		
		// grab user from db
		$user_ids = array();
		foreach(BS_DAO::get_user()->get_users_by_names($auser) as $row)
			$user_ids[$row['id']] = $row['user_name'];
		
		// any user found?
		if(count($user_ids) == 0)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('username_not_found'));
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
				'forum_combo' => BS_ForumUtils::get_recursive_forum_combo(
					'forums['.$id.'][]',isset($forums[$id]) ? $forums[$id] : array(),0,true,false
				)
			);
		}
		
		$tpl->add_variables(array(
			'user' => $user,
			'action_type' => BS_ACP_ACTION_CONFIG_MOD_FORUMS,
			'usernames' => $usernames
		));
	}
}
?>