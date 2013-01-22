<?php
/**
 * Contains the languages module for the ACP
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
 * The languages-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_languages extends BS_ACP_Module
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
		
		$renderer->add_action(BS_ACP_ACTION_ADD_LANGUAGE,'add');
		$renderer->add_action(BS_ACP_ACTION_DELETE_LANGUAGES,'delete');
		$renderer->add_action(BS_ACP_ACTION_UPDATE_LANGUAGES,'update');

		$renderer->add_breadcrumb($locale->lang('acpmod_languages'),BS_URL::build_acpmod_url());
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

		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$names = $cache->get_cache('languages')->get_field_vals_of_keys($ids,'lang_name');
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpmod_url();
			$url->set('ids',implode(',',$ids));
			$url->set('at',BS_ACP_ACTION_DELETE_LANGUAGES);
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->to_url(),
				BS_URL::build_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_UPDATE_LANGUAGES,
			'action_type_add' => BS_ACP_ACTION_ADD_LANGUAGE,
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => stripslashes($search)
		));

		$languages = array();
		foreach($cache->get_cache('languages')->get_elements() as $lang)
		{
			if(!$search || stripos($lang['lang_name'],$search) !== false ||
					stripos($lang['lang_folder'],$search) !== false)
				$languages[] = $lang;
		}
		$tpl->add_variable_ref('languages',$languages);
	}
}
?>