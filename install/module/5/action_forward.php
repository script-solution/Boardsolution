<?php
/**
 * Contains the forward-action of step 5
 * 
 * @package			Boardsolution
 * @subpackage	install.module
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
 * The forward-action
 *
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Action_5_forward extends BS_Install_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		
		$this->set_action_performed(true);
		if($input->isset_var('dir','get'))
			$this->set_redirect(true,$this->get_step_url());
		return '';
	}
}
?>