<?php
/**
 * Contains the content-interface for the BBCode
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
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
 * Represents a BBCode-tag. The most important components are the name, the parameter, the sub-tags
 * and the content
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
interface BS_BBCode_Content
{
	/**
	 * Should generate the replacement for <!--TEXT-->
	 * 
	 * @param string $inner the inner text of the tag
	 * @param string $param the value of the parameter (after the call of get_param()).
	 * @return string the replacement for <!--TEXT-->
	 */
	public function get_text($inner,$param);
	
	/**
	 * Should generate the replacement for <!--PARAM-->. If the parameter is not valid the method
	 * returns false.
	 *
	 * @param string $param the current value of the parameter
	 * @return mixed the replacement for <!--PARAM--> or false if the parameter is invalid
	 */
	public function get_param($param);
}
?>