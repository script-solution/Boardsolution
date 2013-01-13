<?php
/**
 * Contains the backup-class
 * 
 * @package			Boardsolution
 * @subpackage	dba.src.backup
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
 * Represents a backup. That means it contains the prefix, date, size and so on.
 * 
 * @package			Boardsolution
 * @subpackage	dba.src.backup
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Backup_Data extends FWS_Object
{
	/**
	 * the prefix of the backup
	 *
	 * @var string
	 */
	public $prefix = '';
	
	/**
	 * the date of the backup (timestamp)
	 *
	 * @var integer
	 */
	public $date = 0;
	
	/**
	 * the size of all backup files
	 *
	 * @var integer
	 */
	public $size = 0;
	
	/**
	 * the number of files
	 *
	 * @var integer
	 */
	public $files = 0;
	
	/**
	 * constructor
	 * 
	 * @param array $parts the parts of the backup
	 */
	public function __construct($parts)
	{
		parent::__construct();
		
		list($this->prefix,$this->date,$this->files,$this->size) = $parts;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>