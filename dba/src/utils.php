<?php
/**
 * Contains the dbbackup-utils-class
 * 
 * @package			Boardsolution
 * @subpackage	dba.src
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
 * Some utility functions for the dbbackup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Utils extends FWS_Singleton
{
	/**
	 * @return BS_DBA_Utils the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * @return string the selected database
	 */
	public function get_selected_database()
	{
		$user = FWS_Props::get()->user();
		$db = $user->get_session_data('database');
		if($db === false)
			$db = BS_MYSQL_DATABASE;
		
		return $db;
	}
	
	/**
	 * converts a "mysql-date" to a timestamp
	 * 
	 * @param string $date the date in the format: year-month-day hour:minute:second
	 * @return int the corresponding timestamp; -1 if it is an invalid date
	 */
	public function mysql_date_to_time($date)
	{
		return FWS_Date::get_timestamp($date,FWS_Date::TZ_GMT);
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>