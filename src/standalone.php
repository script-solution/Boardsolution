<?php
/**
 * Contains the standalone-base-class
 * 
 * @version			$Id: standalone.php 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The base-class of all standalone-files in BS.
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Standalone extends PLIB_Standalone
{
	public function get_template()
	{
		// by default we don't want to use a template
		return '';
	}
	
	/**
	 * Indicates wether this module requires access to the board (the permission).
	 * The sub-classes may overwrite this method to change the behaviour.
	 * Note that a return-value of "true" requires the sess and auth objects!
	 * 
	 * @return boolean wether this module requires access to the board
	 */
	public function require_board_access()
	{
		return true;
	}
}
?>