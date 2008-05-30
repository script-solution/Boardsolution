<?php
/**
 * Contains the error-logger-class
 *
 * @version			$Id: logger.php 726 2008-05-22 16:10:43Z nasmussen $
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
final class BS_Error_Logger extends PLIB_FullObject implements PLIB_Error_Logger
{
	/**
	 * Limit the number of logs. This stores the number of already logged errors
	 *
	 * @var int
	 */
	private $_log_count = 0;
	
	/**
	 * @see PLIB_Error_Logger::log()
	 *
	 * @param int $no
	 * @param string $msg
	 * @param string $file
	 * @param int $line
	 * @param array $backtrace
	 */
	public function log($no,$msg,$file,$line,$backtrace)
	{
		if(method_exists($this->db,'sql_qry') && isset($this->cfg['enable_error_log']))
		{
			if($this->cfg['enable_error_log'] && $this->_log_count < 5)
			{
				$phpself = $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING);
				$querystr = $this->input->get_var('QUERY_STRING','server',PLIB_Input::STRING);
				$query = $phpself.'?'.$querystr;
				
				$msg = ($no > 0 ? $no.': ' : '').$msg;
				
				$sbacktrace = '';
				if($backtrace !== null)
				{
					$plainbt = new PLIB_Error_BTPrinter_Plain();
					$sbacktrace = $plainbt->print_backtrace($backtrace);
				}
				else
					$sbacktrace = $file.', '.$line;
				
				BS_DAO::get_logerrors()->create(array(
					'query' => $query,
					'user_id' => $this->user->get_user_id(),
					'date' => time(),
					'message' => addslashes($msg),
					'backtrace' => addslashes($sbacktrace)
				));
				$this->_log_count++;
			}
		}
	}

	/**
	 * @see PLIB_Object::_get_print_vars()
	 *
	 * @return array
	 */
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>