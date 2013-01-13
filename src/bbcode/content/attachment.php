<?php
/**
 * Contains the attachment-bbcode-content class
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
		$murl->set('path',$param);
		$murl->set_path('');
		$murl->set_file('<BSP>standalone.php');
		return '<a href="'.$murl->to_url().'">'.$inner.'</a>';
	}
	
	public function get_param($param)
	{
		return trim($param);
	}
}
?>