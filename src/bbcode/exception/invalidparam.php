<?php
/**
 * Contains the bbcode-invalid-param-exception-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The exception for parameter-errors
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Exception_InvalidParam extends BS_BBCode_Exception
{
	/**
	 * Constructor
	 *
	 * @param string $tagname the name of the tag
	 * @param string $param the parameter
	 * @param string $paramtype the type of the parameter (idenitifer, integer, color, url, mail, text)
	 */
	public function __construct($tagname,$param,$paramtype)
	{
		$locale = FWS_Props::get()->locale();
		parent::__construct(sprintf(
			$locale->lang('error_bbcode_invalid_param_'.$paramtype),$param,
			$tagname
		));
	}
}
?>