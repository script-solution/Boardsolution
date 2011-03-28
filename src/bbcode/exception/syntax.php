<?php
/**
 * Contains the bbcode-syntax-exception-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The exception for syntax-errors
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Exception_Syntax extends BS_BBCode_Exception
{
	/**
	 * Constructor
	 *
	 * @param string $text the posted text
	 * @param int $position the position of the error
	 * @param int $errno the error-code
	 */
	public function __construct($text,$position,$errno)
	{
		$locale = FWS_Props::get()->locale();
		parent::__construct(sprintf(
			$locale->lang('error_bbcode_'.$errno),
			FWS_StringHelper::get_text_part($text,$position,20),
			$position
		));
	}
}
?>