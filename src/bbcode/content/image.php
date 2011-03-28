<?php
/**
 * Contains the image-bbcode-content class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The image-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_Image extends BS_BBCode_Content_Default
{
	public function get_text($inner,$param)
	{
		BS_BBCode_Helper::get_instance()->increment_images();
		$inner = trim($inner);
		$inner = str_replace(array("\n",'&quot;'),'',$inner);
		return $inner;
	}
}
?>