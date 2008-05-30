<?php
/**
 * Contains the content-interface for the BBCode
 *
 * @version			$Id: content.php 795 2008-05-29 18:22:45Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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