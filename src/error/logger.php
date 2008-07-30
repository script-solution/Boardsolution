<?php
/**
 * Contains the error-logger-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.error
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The error-logger for Boardsolution
 *
 * @package			Boardsolution
 * @subpackage	src.error
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Error_Logger extends FWS_Object implements FWS_Error_Logger
{
	/**
	 * Limit the number of logs. This stores the number of already logged errors
	 *
	 * @var int
	 */
	private $_log_count = 0;
	
	/**
	 * @see FWS_Error_Logger::log()
	 *
	 * @param int $no
	 * @param string $msg
	 * @param string $file
	 * @param int $line
	 * @param array $backtrace
	 */
	public function log($no,$msg,$file,$line,$backtrace)
	{
		$cfg = FWS_Props::get()->cfg();
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();

		if(isset($cfg['enable_error_log']))
		{
			if($cfg['enable_error_log'] && $this->_log_count < 5)
			{
				$phpself = $input->get_var('PHP_SELF','server',FWS_Input::STRING);
				$querystr = $input->get_var('QUERY_STRING','server',FWS_Input::STRING);
				$query = $phpself.'?'.$querystr;
				
				$msg = ($no > 0 ? $no.': ' : '').$msg;
				
				$sbacktrace = '';
				if($backtrace !== null)
				{
					$plainbt = new FWS_Error_BTPrinter_Plain();
					$sbacktrace = $plainbt->print_backtrace($backtrace);
				}
				else
					$sbacktrace = $file.', '.$line;
				
				BS_DAO::get_logerrors()->create(array(
					'query' => $query,
					'user_id' => $user->get_user_id(),
					'date' => time(),
					'message' => addslashes($msg),
					'backtrace' => addslashes($sbacktrace)
				));
				$this->_log_count++;
			}
		}
	}

	/**
	 * @see FWS_Object::get_print_vars()
	 *
	 * @return array
	 */
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>