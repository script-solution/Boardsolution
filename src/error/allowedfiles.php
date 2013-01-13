<?php
/**
 * Contains the allowed-files-class
 * 
 * @package			Boardsolution
 * @subpackage	src.error
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
 * The allowed-files-listener for Boardsolution. Excludes some sensitive files from being
 * displayed in backtraces.
 *
 * @package			Boardsolution
 * @subpackage	src.error
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Error_AllowedFiles extends FWS_Object implements FWS_Error_AllowedFiles
{
	/**
	 * The excluded files
	 *
	 * @var array
	 */
	private $_exclude = array(
		'config/actions.php',
		'config/dbbackup.php',
		'config/general.php',
		'config/mysql.php',
		'config/userdef.php',
		'dba/access.php'
	);

	/**
	 * @see FWS_Error_AllowedFiles::can_display_file()
	 *
	 * @param string $file
	 * @return boolean
	 */
	public function can_display_file($file)
	{
		foreach($this->_exclude as $e)
		{
			if(stripos($file,$e) !== false)
				return false;
		}
		return true;
	}

	/**
	 * @see FWS_Object::get_dump_vars()
	 *
	 * @return array
	 */
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>