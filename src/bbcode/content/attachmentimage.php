<?php
/**
 * Contains the attachmentimage-bbcode-content class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The attachmentimage-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_AttachmentImage extends BS_BBCode_Content_Default
{
	public function get_text($inner,$param)
	{
		$cfg = FWS_Props::get()->cfg();

		$inner = trim($inner);
		list($att_width,$att_height) = explode('x',$cfg['attachments_images_size']);
		$murl = BS_URL::get_standalone_url('download');
		$murl->set('path',$inner);
		
		$img_url = BS_URL::get_standalone_url('thumbnail');
		$img_url->set('path',$inner);
		$img_url->set('width',$att_width);
		$img_url->set('height',$att_height);
		$img_url->set('method',$cfg['attachments_images_resize_method']);
		
		$content = '<a href="'.$murl->to_url().'">';
		$content .= '<img src="'.$img_url->to_url().'" alt="'.$inner.'" />';
		$content .= '</a>';
		
		return $content;
	}
}
?>