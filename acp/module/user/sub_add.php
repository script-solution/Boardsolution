<?php
/**
 * Contains the add-submodule for user
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
 * The add sub-module for the user-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_user_add extends BS_ACP_SubModule
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
		
		$renderer->add_action(BS_ACP_ACTION_USER_ADD,'add');
		$renderer->add_breadcrumb($locale->lang('register_user'),BS_URL::build_acpsub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cache = FWS_Props::get()->cache();
		$tpl = FWS_Props::get()->tpl();

		$this->request_formular();

		// group combos
		$groups = array();
		foreach($cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
				$groups[$gdata['id']] = $gdata['group_title'];
		}
		
		$tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_USER_ADD,
			'groups' => $groups,
			'main_group' => BS_STATUS_USER,
			'other_groups' => array()
		));
	}
}
?>