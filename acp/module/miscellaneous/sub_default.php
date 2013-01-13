<?php
/**
 * Contains the default-submodule for miscellaneous
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
 * The default sub-module for the miscellaneous-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_miscellaneous_default extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();

		// we have to clear the position here to ensure that we will start again
		// if the last progress hasn't be cleaned up, however.
		$storage = new FWS_Progress_Storage_Session('misc_');
		$storage->clear();
		
		$tasks = BS_ACP_Module_miscellaneous::get_tasks();
		$tpl->add_variable_ref('tasks',$tasks);
	}
}
?>