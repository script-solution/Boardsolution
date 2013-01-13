<?php
/**
 * Contains the bbcode-syntax-exception-class
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
 * The exception for syntax-errors
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Exception_Syntax extends BS_BBCode_Exception
{
	/**
	 * Constructor
	 *
	 * @param string $text the posted text
	 * @param int $position the position of the error
	 * @param int $errno the error-code
	 */
	public function __construct($text,$position,$errno)
	{
		$locale = FWS_Props::get()->locale();
		parent::__construct(sprintf(
			$locale->lang('error_bbcode_'.$errno),
			FWS_StringHelper::get_text_part($text,$position,20),
			$position
		));
	}
}
?>