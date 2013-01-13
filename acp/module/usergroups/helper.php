<?php
/**
 * Contains the helper-class for the usergroups
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
 * The helper for the usergroups-module
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_UserGroups_Helper extends FWS_UtilBase
{
	/**
	 * All predefined user-groups
	 * 
	 * @var array
	 */
	private static $_predef_groups = array(
		BS_STATUS_ADMIN,BS_STATUS_USER,BS_STATUS_GUEST
	);
	
	/**
	 * All permissions of the groups
	 *
	 * @var array
	 */
	private static $_permissions = array(
		'is_super_mod','enter_board','view_memberlist','view_linklist',
		'view_stats','view_calendar','view_search','view_userdetails','view_useronline_list',
		'view_online_locations','view_user_online_detail','edit_own_posts',
		'delete_own_posts','edit_own_threads','delete_own_threads','openclose_own_threads',
		'send_mails','always_edit_poll_options','add_new_link','attachments_add',
		'attachments_download','add_cal_event','edit_cal_event','delete_cal_event',
		'subscribe_forums','disable_ip_blocks','view_user_ip',
	);

	/**
	 * An array with the settings which are disallowed (not possible) for guests
	 *
	 * @var array
	 */
	private static $_guest_disallowed = array(
		'is_super_mod','edit_own_posts','delete_own_posts','edit_own_threads',
		 'delete_own_threads','openclose_own_threads','add_new_link','attachments_add',
		 'add_cal_event','edit_cal_event','delete_cal_event','subscribe_forums',
		 'view_user_ip','always_edit_poll_options'
	);
	
	/**
	 * @return array all predefined user-groups
	 */
	public static function get_predef_groups()
	{
		return self::$_predef_groups;
	}
	
	/**
	 * @return array all permissions
	 */
	public static function get_permissions()
	{
		return self::$_permissions;
	}
	
	/**
	 * @return array an array of all permissions that are not allowed for guests
	 */
	public static function get_guest_disallowed()
	{
		return self::$_guest_disallowed;
	}
}
?>