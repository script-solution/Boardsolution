<?php
/**
 * Contains the base-module for the API-modules
 * 
 * @version			$Id: api_module.php 559 2008-04-10 14:45:58Z nasmussen $
 * @package			Boardsolution
 * @subpackage	extern.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-API-module class. All API-modules have to inherit this class and implement the
 * get_data() method.
 * The sub-class has to have the name "BS_API_Module_&lt;filename&gt;"
 * 
 * @package			Boardsolution
 * @subpackage	extern.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_API_Module extends PLIB_FullObject
{
	/**
	 * should do all necessary operations so that one can access all information
	 */
	public abstract function run();
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>