<?php
/**
 * Contains the change-pw- and change-email-task
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
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
 * The task which deletes "dead" change-pw- and change-email-entries
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_change_email_pw extends FWS_Tasks_Base
{
	public function run()
	{
		// delete pw-changes
		BS_DAO::get_changepw()->delete_timedout(BS_DEAD_REG_DELETE_INTERVAL);
		
		// delete email-changes
		BS_DAO::get_changeemail()->delete_timedout(BS_DEAD_REG_DELETE_INTERVAL);
	}
}
?>