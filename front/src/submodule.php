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
	/**
	 * The template for the submodule
	 *
	 * @var string
	 */
	private $_template;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$classname = get_class($this);
		$lastus = strrpos($classname,'_');
		$prevlastus = strrpos(FWS_String::substr($classname,0,$lastus),'_');
		$this->_template = FWS_String::strtolower(FWS_String::substr($classname,$prevlastus + 1)).'.htm';
	}
	
	/**
	 * @return string the template to use for this sub-module
	 */
	public final function get_template()
	{
		return $this->_template;
	}
	
	/**
	 * Sets the template for this submodule
	 *
	 * @param string $template the template
	 */
	public final function set_template($template)
	{
		$this->_template = $template;
	}
	
	protected function get_print_vars()
	{
		return array_merge(parent::get_print_vars(),get_object_vars($this));
	}
}
?>