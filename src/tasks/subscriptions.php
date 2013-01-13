<?php
/**
 * Contains the subscriptions-task
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
 * The task which deletes subscriptions of topics which are inactive since a specified
 * period of time
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_subscriptions extends FWS_Tasks_Base
{
	public function run()
	{
		$sub_ids = BS_DAO::get_subscr()->get_timedout_subscr_ids(BS_SUBSCRIPTION_TIMEOUT);
		if(count($sub_ids) > 0)
			BS_DAO::get_subscr()->delete_by_ids($sub_ids);
	}
}
?>