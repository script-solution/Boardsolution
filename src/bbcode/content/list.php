<?php
/**
 * Contains the list-bbcode-content class
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
 * The list-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_List extends BS_BBCode_Content_Default
{
	public function get_text($inner,$param)
	{
		switch($param)
		{
			case 'circle':
			case 'square':
			case 'disc':
				$result = "\n".'<ul type="'.$param.'">';
				$end = '</ul>';
				break;

			case '1':
			case 'I':
			case 'i':
			case 'A':
			case 'a':
				$result = "\n".'<ol type="'.$param.'">';
				$end = '</ol>';
				break;

			default:
				$result = "\n".'<ul>';
				$end = '</ul>';
				break;
		}
		
		$inner = FWS_String::substr($inner,FWS_String::strpos($inner,'[*]') + 3);
		$split = explode('[*]',$inner);
		for($i = 0;$i < count($split);$i++)
			$result .= '	<li>'.$split[$i].'</li>';
		$result .= $end;

		return $result;
	}
}
?>