<?php
/**
 * Contains the attachmentimage-bbcode-content class
 *
 * @version			$Id: attachmentimage.php 676 2008-05-08 09:02:28Z nasmussen $
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
		$inner = trim($inner);
		list($att_width,$att_height) = explode('x',$this->cfg['attachments_images_size']);
		$url = $this->url->get_standalone_url('front','download','&amp;path='.$inner);
		$img_url = $this->url->get_standalone_url(
			'front','thumbnail','&amp;path='.$inner.'&amp;width='
				.$att_width.'&amp;height='.$att_height.'&amp;method='
				.$this->cfg['attachments_images_resize_method']
		);
		$content = '<a href="'.$url.'">';
		$content .= '<img src="'.$img_url.'" alt="'.$inner.'" />';
		$content .= '</a>';
		
		return $content;
	}
}
?>