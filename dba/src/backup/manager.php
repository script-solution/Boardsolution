<?php
/**
 * Contains the backup-manager-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src.backup
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The backup-manager which contains the backups, stores them to file and so on
 * 
 * @package			Boardsolution
 * @subpackage	dba.src.backup
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Backup_Manager extends PLIB_FullObject
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
				if($dir = opendir(PLIB_Path::inner().'dba/backups'))
				{
					while($file = readdir($dir))
					{
						if($file != '.' && $file != '..')
						{
							if(PLIB_FileUtils::get_extension($file) == 'sql' &&
									PLIB_String::starts_with($file,$prefix))
								@unlink(PLIB_Path::inner().'dba/backups/'.$file);
						}
					}
					closedir($dir);
					
					unset($this->_backups[$key]);
					return $this->write_to_file();
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
	 */
	public function add_backup($prefix,$total_files,$size)
	{
		if($this->get_backup($prefix) !== null)
			return false;
		
		$parts = array($prefix,time(),$total_files,$size);
		$backup = new BS_DBA_Backup_Data($parts);
		$this->_backups[] = $backup;
		
		$this->write_to_file();
		return true;
	}
	
	/**
	 * Writes the backups to file
	 *
	 * @return boolean true if successfull
	 */
	public function write_to_file()
	{
		$str = '';
		foreach($this->_backups as $backup)
		{
			$str .= trim($backup->prefix.';'.$backup->date.';'.$backup->files.';'.$backup->size);
			$str .= BS_DBA_LINE_WRAP;
		}
		
		$file = PLIB_Path::inner().'dba/backups/backups.txt';
		$res = PLIB_FileUtils::write($file,$str);
		@chmod($file,0666);
		return $res;
	}
	
	/**
	 * parses the file and stores the found backups including the attributes
	 *
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
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>