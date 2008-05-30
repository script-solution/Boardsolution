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
final class BS_DBA_Utils extends PLIB_Singleton
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
		return isset($_SESSION['database']) ? $_SESSION['database'] : BS_MYSQL_DATABASE;
	}
	
	/**
	 * converts a "mysql-date" to a timestamp
	 * 
	 * @param string $date the date in the format: year-month-day hour:minute:second
	 * @return int the corresponding timestamp; -1 if it is an invalid date
	 */
	public function mysql_date_to_time($date)
	{
		// TODO finish!
		/*if(!$date)
			return -1;
		
		list($date_str,$time_str) = explode(' ',$date);
		list($year,$month,$day) = explode('-',$date_str);
		list($hour,$min,$sec) = explode(':',$time_str);*/
		
		return PLIB_Date::get_timestamp($date,PLIB_Date::TZ_GMT);
		//return mktime($hour,$min,$sec,$month,$day,$year);
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>