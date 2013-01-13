<?php
/**
 * Contains the base-module for the API-modules
 * 
 * @package			Boardsolution
 * @subpackage	extern.src
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
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>