<?php
/**
 * Contains the export-community-interface
 * 
 * @package			Boardsolution
 * @subpackage	src.community
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
 * The interface for the "community-export". "Export" means that another system uses Boardsolution
 * as community. Therefore the registration, user-management, activation and so on will be done
 * by Boardsolution and the other system fetches the users from Boardsolution to login a user
 * in the other system.
 * <br>
 * So if you want to do this you have to implement this interface and you may react on the events
 * that are fired. For example if a user registers in Boardsolution, BS fires the event
 * 'user_registered' and gives you the user-data of the user that has registered. So you can
 * do this for your system if you like.
 *
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 */
interface BS_Community_Export
{
	/**
	 * Will be called as soon as a new user has been registered
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function user_registered($user);
	
	/**
	 * Will be called as soon as an attribute of the user has changed
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function user_changed($user);
	
	/**
	 * Will be called as soon as a user has been reactivated
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function user_reactivated($user);
	
	/**
	 * Will be called as soon as a user has been deactivated
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function user_deactivated($user);
	
	/**
	 * Will be called as soon as a user has been deleted
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function user_deleted($user);
}
?>