<?php
/**
 * Contains the size-bbcode-content class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The size-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_Size extends BS_BBCode_Content_Default
{
	public function get_param($param)
	{
		$iparam = (int)$param;
		if($iparam >= 8 && $iparam <= 29)
			return $iparam;
		
		return false;
	}
}
?>