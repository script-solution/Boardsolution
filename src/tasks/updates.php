<?php
/**
 * Contains the updates-task
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
 * The task which checks for Boardsolution-Updates
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_updates extends FWS_Tasks_Base
{
	public function run()
	{
		// load versions-file
		$http = new FWS_HTTP(BS_Version::VERSION_HOST);
		$versions = $http->get(BS_Version::VERSION_PATH);
		if($versions === false)
			return;
		
		// check for updates
		$vs = BS_Version::read_versions($versions);
		$res = BS_Version::check_for_update($vs);
		if($res !== null)
		{
			$mail = BS_EmailFactory::get_instance()->get_updates_mail(is_array($res));
			foreach(BS_DAO::get_profile()->get_users_by_groups(array(BS_STATUS_ADMIN)) as $user)
				$mail->add_bcc_recipient($user['user_email']);
			$mail->send_mail();
		}
	}
}
?>