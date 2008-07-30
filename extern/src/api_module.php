<?php
/**
 * Contains the base-module for the API-modules
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	extern.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-API-module class. All API-modules have to inherit this class and implement the
 * run() method.
 * The sub-class has to have the name "BS_API_Module_&lt;filename&gt;"
 * 
 * @package			Boardsolution
 * @subpackage	extern.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_API_Module extends FWS_Object
{
	/**
	 * should do all necessary operations so that one can access all information
	 */
	public abstract function run();
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>