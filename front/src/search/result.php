<?php
/**
 * Contains the search-result-interface
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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