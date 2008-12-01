<?php
/**
 * Contains the helper-class for the task-module
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An helper-class for the tasks-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Tasks_Helper extends FWS_Singleton
{
	/**
	 * @return BS_ACP_Module_Tasks_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Checks wether the given filename belongs to a default-task
	 *
	 * @param string $name the name of the file
	 * @return boolean true if it is a default one
	 */
	public function is_default_task($name)
	{
		$default = array(
			'attachments.php','change_email_pw.php','email_notification.php',
			'error_log.php','events.php','logged_ips.php','registrations.php',
			'subscriptions.php'
		);
		return in_array($name,$default);
	}
	
	/**
	 * @return array an associative array with the interval-types and the corresponding name
	 */
	public function get_interval_types()
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
	public function encode_interval($interval,$type)
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
	public function decode_interval($interval)
	{
		if($interval % 86400 == 0)
			return array($interval / 86400,'days');
		
		if($interval % 3600 == 0)
			return array($interval / 3600,'hours');
		
		return array($interval / 60,'minutes');
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>