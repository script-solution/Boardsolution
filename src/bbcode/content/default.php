<?php
/**
 * Contains the default-bbcode-content class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_BBCode_Content_Default extends FWS_Object implements BS_BBCode_Content
{
	/**
	 * The tag-id
	 *
	 * @var int
	 */
	protected $_id;
	
	/**
	 * Constructor
	 *
	 * @param int $id the tag-id
	 */
	public function __construct($id)
	{
		parent::__construct();
		
		$this->_id = $id;
	}
	
	public function get_text($inner,$param)
	{
		return $inner;
	}
	
	public function get_param($param)
	{
		return $param;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>