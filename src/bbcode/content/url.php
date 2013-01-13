<?php
/**
 * Contains the url-bbcode-content class
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
 * The url-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_URL extends BS_BBCode_Content_Default
{
	public function get_text($inner,$param)
	{
		if($param != '')
			$url = str_replace(array("\n",'&quot;'),'',$inner);
		else
			$url = BS_BBCode_Helper::get_instance()->parse_url($inner);
		
		return $url;
	}
	
	public function get_param($param)
	{
		if($param != '')
			return BS_BBCode_Helper::get_instance()->parse_url($param);
		
		return '';
	}
}
?>