<?php
/**
 * Contains the attachmentimage-bbcode-content class
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
		$murl->set_path('');
		$murl->set_file('<BSP>standalone.php');
		
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