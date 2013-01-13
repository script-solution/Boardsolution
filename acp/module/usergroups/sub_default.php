<?php
/**
 * Contains the default-submodule for usergroups
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
 * The default sub-module for the usergroups-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_usergroups_default extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_DELETE_USER_GROUPS,'delete');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$cache = FWS_Props::get()->cache();
		$auth = FWS_Props::get()->auth();
		$tpl = FWS_Props::get()->tpl();

		if(($delete = $input->get_var('delete','post')) != null)
		{
			$ids = implode(',',$delete);
			$names = array();
			foreach(BS_DAO::get_usergroups()->get_by_ids($delete) as $group)
				$names[] = $group['group_title'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpsub_url();
			$url->set('at',BS_ACP_ACTION_DELETE_USER_GROUPS);
			$url->set('ids',$ids);
			
			$functions->add_delete_message(
				$locale->lang('delete_group_notice').'<br /><br />'
					.sprintf($locale->lang('delete_message'),$namelist),
				$url->to_url(),
				BS_URL::build_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		$predef_groups = BS_ACP_Module_UserGroups_Helper::get_predef_groups();
		
		$groups = array();
		foreach($cache->get_cache('user_groups') as $data)
		{
			if(!$search || stripos($data['group_title'],$search) !== false)
			{
				$groups[] = array(
					'id' => $data['id'],
					'group_name' => $auth->get_colored_groupname($data['id']),
					'is_visible' => BS_ACP_Utils::get_yesno($data['is_visible']),
					'is_super_mod' => BS_ACP_Utils::get_yesno($data['is_super_mod']),
					'is_no_predefined_group' => !in_array($data['id'],$predef_groups)
				);
			}
		}

		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variable_ref('groups',$groups);
		$tpl->add_variables(array(
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => $search
		));
	}
}
?>