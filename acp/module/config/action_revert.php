<?php
/**
 * Contains the revert-cfgitems-action
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
 * The revert-cfgitems-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_Config_revert extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';
		
		$helper = BS_ACP_Module_Config_Helper::get_instance();
		if($helper->get_manager()->revert_item($id))
		{
			$cache->refresh('config');
			
			$this->set_action_performed(true);
			$this->set_success_msg($locale->lang('setting_reverted'));
		}
		
		$this->set_action_performed(true);

		return '';
	}
}
?>