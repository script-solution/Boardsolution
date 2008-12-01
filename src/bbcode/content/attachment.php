<?php
/**
 * Contains the attachment-bbcode-content class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The attachment-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_Attachment extends BS_BBCode_Content_Default
{
	public function get_text($inner,$param)
	{
		$murl = BS_URL::get_standalone_url('download');
		$murl->set('download',$param);
		return '<a href="'.$murl->to_url().'">'.$inner.'</a>';
	}
	
	public function get_param($param)
	{
		return trim($param);
	}
}
?>