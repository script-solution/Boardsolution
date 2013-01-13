<?php
/**
 * Contains the backup-manager-class
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
 * The backup-manager which contains the backups, stores them to file and so on
 * 
 * @package			Boardsolution
 * @subpackage	dba.src.backup
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Backup_Manager extends FWS_Object
{
	/**
	 * The file which contains the backups
	 *
	 * @var string
	 */
	private $_file = '';
	
	/**
	 * The backups. An numeric array with instances of BS_DBA_backup
	 *
	 * @var array
	 */
	private $_backups = array();
	
	/**
	 * constructor
	 * 
	 * @param string $file the backup-file
	 */
	public function __construct($file)
	{
		parent::__construct();
		
		$this->_file = $file;
		$this->_parse_file();
	}
	
	/**
	 * @return array all backups
	 */
	public function get_backups()
	{
		return $this->_backups;
	}
	
	/**
	 * searches for the backup with given prefix
	 * 
	 * @param string $prefix the prefix of the backup
	 * @return object the BS_DBA_backup object or null if not found
	 */
	public function get_backup($prefix)
	{
		foreach($this->_backups as $backup)
		{
			if($backup->prefix == $prefix)
				return $backup;
		}
		
		$temp = null;
		return $temp;
	}
	
	/**
	 * Deletes the backup with given prefix
	 * 
	 * @param string $prefix the prefix of the backup
	 * @return boolean true if successfull
	 */
	public function delete_backup($prefix)
	{
		foreach($this->_backups as $key => $backup)
		{
			if($backup->prefix == $prefix)
			{
				if($dir = opendir(FWS_Path::server_app().'dba/backups'))
				{
					while($file = readdir($dir))
					{
						if($file != '.' && $file != '..')
						{
							if(FWS_FileUtils::get_extension($file) == 'sql' &&
									FWS_String::starts_with($file,$prefix))
								@unlink(FWS_Path::server_app().'dba/backups/'.$file);
						}
					}
					closedir($dir);
					
					unset($this->_backups[$key]);
					return $this->write_to_file() !== false;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Adds a backup with given values
	 * 
	 * @param string $prefix the prefix
	 * @param int $total_files the number of files
	 * @param int $size the complete size
	 * @return boolean true if successfull
	 */
	public function add_backup($prefix,$total_files,$size)
	{
		if($this->get_backup($prefix) !== null)
			return false;
		
		$parts = array($prefix,time(),$total_files,$size);
		$backup = new BS_DBA_Backup_Data($parts);
		$this->_backups[] = $backup;
		
		return $this->write_to_file() !== false;
	}
	
	/**
	 * Writes the backups to file
	 *
	 * @return bool|int the number of written bytes or false
	 */
	public function write_to_file()
	{
		$str = '';
		foreach($this->_backups as $backup)
		{
			$str .= trim($backup->prefix.';'.$backup->date.';'.$backup->files.';'.$backup->size);
			$str .= BS_DBA_LINE_WRAP;
		}
		
		$file = FWS_Path::server_app().'dba/backups/backups.txt';
		$res = FWS_FileUtils::write($file,$str);
		@chmod($file,0666);
		return $res;
	}
	
	/**
	 * Parses the file and stores the found backups including the attributes
	 */
	private function _parse_file()
	{
		$lines = @file($this->_file);
		if(!$lines)
			$lines = array();
		
		foreach($lines as $line)
		{
			$parts = explode(';',$line);
			if(count($parts) == 4)
				$this->_backups[] = new BS_DBA_Backup_Data($parts);
		}
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>