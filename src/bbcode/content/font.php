<?php
/**
 * Contains the font-bbcode-content class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The font-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_Font extends BS_BBCode_Content_Default
{
	public function get_param($param)
	{
		$fonts = BS_BBCode_Helper::get_instance()->get_fonts();
		$param = FWS_String::strtolower($param);
		if(in_array($param,$fonts))
			return $param;
		
		return false;
	}
}
?>