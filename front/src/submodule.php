<?php
/**
 * Contains the front-sub-module-base-class
 * 
 * @package			Boardsolution
 * @subpackage	front.src
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	
	protected function get_dump_vars()
	{
		return array_merge(parent::get_dump_vars(),get_object_vars($this));
	}
}
?>