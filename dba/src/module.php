<?php
/**
 * Contains the module for the dbbackup-script
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module-base class for all dbbackup-modules
 * 
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_DBA_Module extends PLIB_Module
{
	public function get_location()
	{
		return array();
	}
}
?>