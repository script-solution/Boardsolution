<?php
/**
 * Contains the helper-class for the task-module
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
 * An helper-class for the tasks-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Tasks_Helper extends FWS_UtilBase
{
	/**
	 * Checks wether the given filename belongs to a default-task
	 *
	 * @param string $name the name of the file
	 * @return boolean true if it is a default one
	 */
	public static function is_default_task($name)
	{
		$default = array(
			'attachments.php','change_email_pw.php','email_notification.php',
			'error_log.php','events.php','logged_ips.php','registrations.php',
			'subscriptions.php','updates.php'
		);
		return in_array($name,$default);
	}
	
	/**
	 * @return array an associative array with the interval-types and the corresponding name
	 */
	public static function get_interval_types()
	{
		$locale = FWS_Props::get()->locale();
		
		return array(
			'days' => $locale->lang('days'),
			'hours' => $locale->lang('hours'),
			'minutes' => $locale->lang('minutes')
		);
	}
	
	/**
	 * Converts the given interval and interval-type to the corresponding number of seconds
	 *
	 * @param int $interval the amount of the given type
	 * @param string $type the type: days, hours or minutes
	 * @return int the number of seconds
	 */
	public static function encode_interval($interval,$type)
	{
		switch($type)
		{
			case 'days':
				return $interval * 86400;
			case 'hours':
				return $interval * 3600;
			case 'minutes':
				return $interval * 60;
			default:
				FWS_Helper::error('Invalid value for $type ("'.$type.'")!');
				return 0;
		}
	}
	
	/**
	 * Decodes the interval to display from the given interval in seconds
	 * 
	 * @param int $interval the interval in seconds
	 * @return array an array of the form: <code>array(<count>,<unit>)</code>
	 */
	public static function decode_interval($interval)
	{
		if($interval % 86400 == 0)
			return array($interval / 86400,'days');
		
		if($interval % 3600 == 0)
			return array($interval / 3600,'hours');
		
		return array($interval / 60,'minutes');
	}
}
?>