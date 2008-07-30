<?php
/**
 * Contains the dbbackup-utils-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>