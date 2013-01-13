<?php
/**
 * Contains the default-bbcode-content class
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