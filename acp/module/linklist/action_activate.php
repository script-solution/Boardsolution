<?php
/**
 * Contains the (de)activate-links-action
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
 * The (de)activate-links-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_linklist_activate extends BS_ACP_Action_Base
{
	public function perform_action($active = 0)
	{
		$input = FWS_Props::get()->input();

		$ids = $input->get_var('delete','post');
		if(!FWS_Array_Utils::is_integer($ids) || count($ids) == 0)
			return 'Got an invalid id-string via GET';
		
		BS_DAO::get_links()->set_active($ids,$active == 0 ? 0 : 1);
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>