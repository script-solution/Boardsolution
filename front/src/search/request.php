<?php
/**
 * Contains the search-request-interface
 *
 * @version			$Id: request.php 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The interface for all search-request-types
 *
 * @package			Boardsolution
 * @subpackage	front.src.search
 * @author			Nils Asmussen <nils@script-solution.de>
 */
interface BS_Front_Search_Request
{
	/**
	 * Should return the name of this request-type
	 * 
	 * @return string the name
	 */
	public function get_name();
	
	/**
	 * Should return the result-type that should be used. This method will be called when the
	 * search should be initialized. That means for example if the user has submitted the search-
	 * formular and has choosen the result-type the method should retrieve the choosen type
	 * and return it.
	 *
	 * @return string the name of the result-type that should be used
	 * @see set_result_type()
	 */
	public function get_initial_result_type();
	
	/**
	 * Will be called by the search-manager if an existing search should be used.
	 *
	 * @param string $result the name of the result you should use
	 * @see get_initial_result_type()
	 */
	public function set_result_type($result);
	
	/**
	 * @return BS_Front_Search_Result the result-object
	 */
	public function get_result();
	
	/**
	 * Should return the order of the elements in the result. The following order-values are
	 * possible:
	 * <ul>
	 * 	<li>topic_name</li>
	 * 	<li>topic_type</li>
	 * 	<li>replies</li>
	 * 	<li>views</li>
	 * 	<li>date</li>
	 * </ul>
	 * Additionally you have to return the direction via ASC or DESC.
	 *
	 * @return array the order in the following form:
	 * 	<code>array(<order>,<ad>)</code>
	 */
	public function get_order();
	
	/**
	 * Should return an array of all keywords that should be highlighted
	 *
	 * @return array an numeric array with the keywords to highlight
	 */
	public function get_highlight_keywords();
	
	/**
	 * Should decode all entered keywords as string so that it can be stored somewhere
	 * 
	 * @return string the keywords
	 * @see decode_keywords()
	 */
	public function encode_keywords();
	
	/**
	 * Should decode the given keywords and may store it internally for later usage.
	 *
	 * @param string $keywords the encoded keywords
	 * @see encode_keywords()
	 */
	public function decode_keywords($keywords);
	
	/**
	 * Should generate the title of the search
	 *
	 * @param BS_Front_Search_Manager $search the search-manager
	 * @return string the title
	 */
	public function get_title($search);
	
	/**
	 * Should build the SQL-statement, search and return an array of all found ids.
	 * If an error occurrs it has to add it via <var>$this->msgs->add_<type>()</var>!
	 * 
	 * @return array an array with the found ids or null if an error occurred
	 */
	public function get_result_ids();
}
?>