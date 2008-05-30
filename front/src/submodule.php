<?php
/**
 * Contains the front-sub-module-base-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The sub-module-base class for all Front-modules
 * 
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_SubModule extends BS_Front_Module
{
	public function get_template()
	{
		$classname = get_class($this);
		$lastus = strrpos($classname,'_');
		$prevlastus = strrpos(PLIB_String::substr($classname,0,$lastus),'_');
		return PLIB_String::strtolower(PLIB_String::substr($classname,$prevlastus + 1)).'.htm';
	}
}
?>