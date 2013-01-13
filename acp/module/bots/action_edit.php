<?php
/**
 * Contains the edit-bot-action
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
 * The edit-bot-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_bots_edit extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';
		
		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			return 'The id "'.$id.'" is invalid';
		
		if(!$cache->get_cache('bots')->key_exists($id))
			return 'A bot with id="'.$id.'" does not exist';
		
		$values = BS_ACP_Module_bots::check_values();
		if(!is_array($values))
			return $values;
		
		BS_DAO::get_bots()->update($id,$values);
		$cache->refresh('bots');
		
		$this->set_success_msg($locale->lang('bot_edit_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>