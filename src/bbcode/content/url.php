<?php
/**
 * Contains the url-bbcode-content class
 *
 * @version			$Id: url.php 795 2008-05-29 18:22:45Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The url-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_URL extends BS_BBCode_Content_Default
{
	public function get_text($inner,$param)
	{
		if($param != '')
			$url = $inner;
		else
			$url = BS_BBCode_Helper::get_instance()->parse_url($inner);
		
		return $url;
	}
	
	public function get_param($param)
	{
		if($param != '')
			return BS_BBCode_Helper::get_instance()->parse_url($param);
		
		return '';
	}
}
?>