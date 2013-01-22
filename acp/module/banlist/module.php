<?php
/**
 * Contains the banlist module for the ACP
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
 * The banlist-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_banlist extends BS_ACP_Module
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
		
		$renderer->add_action(BS_ACP_ACTION_DELETE_BANS,'delete');
		$renderer->add_action(BS_ACP_ACTION_ADD_BAN,'add');
		$renderer->add_action(BS_ACP_ACTION_UPDATE_BANS,'update');

		$renderer->add_breadcrumb($locale->lang('acpmod_bans'),BS_URL::build_acpmod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();

		if(($delete = $input->get_var('delete','post')) != null)
		{
			$names = $cache->get_cache('banlist')->get_field_vals_of_keys($delete,'bann_name');
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpmod_url();
			$url->set('ids',implode(',',$delete));
			$url->set('at',BS_ACP_ACTION_DELETE_BANS);
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->to_url(),
				BS_URL::build_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		$this->request_formular();
		
		$type_array = array(
			'ip' => $locale->lang('ip_address'),
			'user' => $locale->lang('username'),
			'mail' => $locale->lang('email_adress')
		);
	
		$entries = array();
		foreach($cache->get_cache('banlist')->get_elements() as $lang)
		{
			if(!$search || stripos($lang['bann_name'],$search) !== false ||
					stripos($lang['bann_type'],$search) !== false)
				$entries[] = $lang;
		}
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['at']);
		unset($hidden['search']);
		$tpl->add_variables(array(
			'types' => $type_array,
			'entries' => $entries,
			'action_type_update' => BS_ACP_ACTION_UPDATE_BANS,
			'action_type_add' => BS_ACP_ACTION_ADD_BAN,
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => stripslashes($search)
		));
	}
}
?>