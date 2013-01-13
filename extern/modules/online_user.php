<?php
/**
 * Contains the online-user-module
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
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
 * Contains the currently online user, bots and so on.
 * 
 * @package			Boardsolution
 * @subpackage	extern.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_API_Module_online_user extends BS_API_Module
{
	/**
	 * the total number of online "user", that means registered, guests, bots and ghosts
	 *
	 * @var integer
	 */
	public $total_online = 0;

	/**
	 * a numeric array with the names of all currently online bots
	 *
	 * @var array
	 */
	public $online_bots = array();

	/**
	 * an associative array with the online registered user in the following form:
	 * <code>
	 * array(
	 * 	'id' => <idOfTheUser>,
	 * 	'name' => <nameOfTheUser>,
	 * 	'group' => <idOfTheMainUserGroup>,
	 * 	'location' => <theCurrentLocation>
	 * )
	 * </code>
	 *
	 * @var array
	 */
	public $online_user = array();

	/**
	 * the number of currently online guests
	 *
	 * @var integer
	 */
	public $online_guest_num = 0;

	/**
	 * the number of currently online ghosts
	 *
	 * @var integer
	 */
	public $online_ghost_num = 0;

	public function run()
	{
		$sessions = FWS_Props::get()->sessions();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();

		$online = $sessions->get_user_at_location();

		$this->total_online = count($online);
		foreach($online as $data)
		{
			if($data['bot_name'] != '')
				$this->online_bots[] = $data['bot_name'];
			else if($data['user_id'] == 0)
				$this->online_guest_num++;
			else if(!$user->is_admin() && $data['ghost_mode'] == 1 &&
					$cfg['allow_ghost_mode'] == 1)
				$this->online_ghost_num++;
			else
			{
				$loc = new BS_Location($data['location']);
				$this->online_user[] = array(
					'id' => $data['user_id'],
					'name' => $data['user_name'],
					'group' => (int)$data['user_group'],
					'location' => $loc->decode()
				);
			}
		}
	}
}
?>