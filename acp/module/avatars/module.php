<?php
/**
 * Contains the avatar module for the ACP
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
 * The avatar-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_avatars extends BS_ACP_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_DELETE_AVATARS,'delete');
		$renderer->add_action(BS_ACP_ACTION_IMPORT_AVATARS,'import');
		$renderer->add_breadcrumb($locale->lang('acpmod_avatars'),BS_URL::build_acpmod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		$delete = $input->get_var('delete','post');
		if($delete != null)
		{
			$ids = $input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_avatars()->get_by_ids($ids) as $data)
				$names[] = $data['av_pfad'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpmod_url();
			$url->set('ids',implode(',',$ids));
			$url->set('at',BS_ACP_ACTION_DELETE_AVATARS);
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->to_url(),
				BS_URL::build_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		if($search != '')
			$num = BS_DAO::get_avatars()->get_count_for_keyword($search);
		else
			$num = BS_DAO::get_avatars()->get_count();
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'num' => $num,
			'action_type_import' => BS_ACP_ACTION_IMPORT_AVATARS,
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => stripslashes($search)
		));

		$end = 10;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$avatars = array();
		if($search != '')
			$list = BS_DAO::get_avatars()->get_list_for_keyword($search,$pagination->get_start(),$end);
		else
			$list = BS_DAO::get_avatars()->get_list($pagination->get_start(),$end);
		
		foreach($list as $data)
		{
			if($data['user'] == 0)
				$data['owner'] = $locale->lang('administrator');
			else
				$data['owner'] = BS_ACP_Utils::get_userlink($data['user'],$data['user_name']);
			$avatars[] = $data;
		}

		$tpl->add_variable_ref('avatars',$avatars);
		$murl = BS_URL::get_acpmod_url();
		$murl->set('search',$search);
		$pagination->populate_tpl($murl);
	}
}
?>