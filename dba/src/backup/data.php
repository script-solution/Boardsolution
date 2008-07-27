<?php
/**
 * Contains the backup-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src.backup
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Represents a backup. That means it contains the prefix, date, size and so on.
 * 
 * @package			Boardsolution
 * @subpackage	dba.src.backup
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Backup_Data extends PLIB_Object
{
	/**
	 * the prefix of the backup
	 *
	 * @var string
	 */
	public $prefix = '';
	
	/**
	 * the date of the backup (timestamp)
	 *
	 * @var integer
	 */
	public $date = 0;
	
	/**
	 * the size of all backup files
	 *
	 * @var integer
	 */
	public $size = 0;
	
	/**
	 * the number of files
	 *
	 * @var integer
	 */
	public $files = 0;
	
	/**
	 * constructor
	 * 
	 * @param array $parts the parts of the backup
	 */
	public function __construct($parts)
	{
		parent::__construct();
		
		list($this->prefix,$this->date,$this->files,$this->size) = $parts;
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>