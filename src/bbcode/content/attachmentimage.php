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
		$url = PLIB_Props::get()->url();
		$cfg = PLIB_Props::get()->cfg();

		$inner = trim($inner);
		list($att_width,$att_height) = explode('x',$cfg['attachments_images_size']);
		$murl = $url->get_url('download','&amp;path='.$inner);
		$img_url = $url->get_url(
			'thumbnail','&amp;path='.$inner.'&amp;width='
				.$att_width.'&amp;height='.$att_height.'&amp;method='
				.$cfg['attachments_images_resize_method']
		);
		$content = '<a href="'.$murl.'">';
		$content .= '<img src="'.$img_url.'" alt="'.$inner.'" />';
		$content .= '</a>';
		
		return $content;
	}
}
?>