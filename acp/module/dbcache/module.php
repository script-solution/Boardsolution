<?php
/**
 * Contains the dbcache module for the ACP
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
 * The dbcache-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_dbcache extends BS_ACP_Module
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
		
		$renderer->add_action(BS_ACP_ACTION_REGENERATE_CACHE,'regenerate');
		$renderer->add_breadcrumb($locale->lang('acpmod_dbcache'),BS_URL::build_acpmod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();

		$tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_REGENERATE_CACHE
		));
		
		$entries = array();
		foreach($cache->get_caches() as $name => $content)
		{
			if($content instanceof FWS_Cache_Content)
				$entries[] = $name;
		}
		$tpl->add_variable_ref('entries',$entries);
		
		// show details?
		if($input->isset_var('name','get'))
		{
			$name = $input->get_var('name','get',FWS_Input::STRING);
			$content = $cache->get_cache($name);
			if($content != null)
			{
				$tpl->add_variables(array(
					'show_cache' => true,
					'details_name' => $name,
					'cache_content' => FWS_Printer::to_string($content->get_elements())
				));
			}
		}
	}
}
?>