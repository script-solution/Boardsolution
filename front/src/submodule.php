<?php
/**
 * Contains the front-sub-module-base-class
 * 
 * @version			$Id: submodule.php 543 2008-04-10 07:32:51Z nasmussen $
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