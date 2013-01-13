<?php
/**
 * Contains the pmbanlist-userprofile-submodule
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The pmbanlist submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pmbanlist extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_BAN_USER,'pmbanuser');
		$renderer->add_action(BS_ACTION_UNBAN_USER,'pmunbanuser');

		$renderer->add_breadcrumb($locale->lang('banlist'),BS_URL::build_sub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();

		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}

		$del = $input->get_var('del','post');
		if($del != null && FWS_Array_Utils::is_integer($del))
		{
			$ids = implode(',',$del);
			$names = array();
			foreach(BS_DAO::get_userbans()->get_by_user($user->get_user_id(),$del) as $i => $data)
				$names[] = $data['user_name'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));

			$url = BS_URL::get_sub_url();
			$no_url = $url->to_url();
			
			$url->set(BS_URL_AT,BS_ACTION_UNBAN_USER);
			$url->set(BS_URL_DEL,$ids);
			$url->set_sid_policy(BS_URL::SID_FORCE);
			$yes_url = $url->to_url();
			
			$url = BS_URL::get_mod_url('redirect');
			$url->set(BS_URL_LOC,'del_pm_ban');
			$url->set(BS_URL_ID,$ids);
			$target_url = $url->to_url();
			
			$functions->add_delete_message(
				sprintf($locale->lang('banlist_delete'),$namelist),$yes_url,$no_url,$target_url
			);
		}

		$tpl->add_variables(array(
			'action_param' => BS_URL_ACTION
		));
		
		$banned_user = array();
		foreach(BS_DAO::get_userbans()->get_all_of_user($user->get_user_id()) as $i => $data)
		{
			$banned_user[] = array(
				'number' => $i + 1,
				'user_name' => BS_UserUtils::get_link($data['baned_user'],$data['user_name'],
					$data['user_group']),
				'id' => $data['id']
			);
		}
		
		$tpl->add_variable_ref('banned_user',$banned_user);
	
		$url = BS_URL::get_sub_url();
		$url->set(BS_URL_AT,BS_ACTION_BAN_USER);
		$url->set_sid_policy(BS_URL::SID_FORCE);
		$tpl->add_variables(array(
			'ban_user_url' => $url->to_url()
		));
	}
}
?>