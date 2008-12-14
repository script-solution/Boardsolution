<?php
/**
 * Contains the allowed-files-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.error
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The allowed-files-listener for Boardsolution. Excludes some sensitive files from being
 * displayed in backtraces.
 *
 * @package			Boardsolution
 * @subpackage	src.error
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Error_AllowedFiles extends FWS_Object implements FWS_Error_AllowedFiles
{
	/**
	 * The excluded files
	 *
	 * @var array
	 */
	private $_exclude = array(
		'config/actions.php',
		'config/community.php',
		'config/dbbackup.php',
		'config/general.php',
		'config/mysql.php',
		'config/userdef.php',
		'dba/access.php'
	);

	/**
	 * @see FWS_Error_AllowedFiles::can_display_file()
	 *
	 * @param string $file
	 * @return boolean
	 */
	public function can_display_file($file)
	{
		foreach($this->_exclude as $e)
		{
			if(stripos($file,$e) !== false)
				return false;
		}
		return true;
	}

	/**
	 * @see FWS_Object::get_dump_vars()
	 *
	 * @return array
	 */
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>