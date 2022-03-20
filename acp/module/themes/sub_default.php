<?php
/**
 * Contains the default-submodule for themes
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
 * The default sub-module for the themes-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_themes_default extends BS_ACP_SubModule
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
		$renderer->add_action(BS_ACP_ACTION_UPDATE_THEMES,'update');
		$renderer->add_action(BS_ACP_ACTION_DELETE_THEMES,'delete');
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

		$delete = $input->get_var('delete','post');
		if($delete != null)
		{
			$names = $cache->get_cache('themes')->get_field_vals_of_keys($delete,'theme_name');
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$url = BS_URL::get_acpsub_url();
			$url->set('at',BS_ACP_ACTION_DELETE_THEMES);
			$url->set('ids',implode(',',$delete));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->to_url(),
				BS_URL::build_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		$this->request_formular();
		
		$tpl->add_variables(array(
			'at_update' => BS_ACP_ACTION_UPDATE_THEMES
		));
		
		$eurl = BS_URL::get_acpsub_url(0,'editor');
		
		$themes = array();
		foreach($cache->get_cache('themes') as $data)
		{
			if(!$search || stripos($data['theme_name'],$search) !== false ||
				stripos($data['theme_folder'],$search) !== false)
			{
				$eurl->set('theme',$data['theme_folder']);
				$themes[] = array(
					'id' => $data['id'],
					'theme_name' => $data['theme_name'],
					'theme_folder' => $data['theme_folder'],
					'edit_url' => $eurl->to_url()
				);
			}
		}
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variable_ref('themes',$themes);
		$tpl->add_variables(array(
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => $search !== null ? stripslashes($search) : ''
		));
	}
}
?>