<?php
/**
 * Contains the search-result-interface
 * 
 * @package			Boardsolution
 * @subpackage	front.src.search
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
 * The interface for all possible search-result-types
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
interface BS_Front_Search_Result
{
	/**
	 * Should return the name of this result-type
	 * 
	 * @return string the name
	 */
	public function get_name();
	
	/**
	 * Should display the search-result for the given search
	 *
	 * @param BS_Front_Search_Manager $search the search-object
	 * @param BS_Front_Search_Request $request the request-object
	 */
	public function display_result($search,$request);
	
	/**
	 * Should return the name of the template that should be used for the result
	 *
	 * @return string the template-name
	 */
	public function get_template();
	
	/**
	 * @return string the message which should be displayed if no results have been found
	 */
	public function get_noresults_message();
}
?>