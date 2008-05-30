<?php
/**
 * Contains the restore-task-class
 *
 * @version			$Id: restore.php 688 2008-05-10 16:04:42Z nasmussen $
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The restore-task
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_RestoreBackup_Tasks_Restore extends PLIB_FullObject
	implements PLIB_Progress_Task
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		// init session
		if(!isset($_SESSION['BS_restore']))
		{
			// ensure that we start a new progress
			BS_DBA_Progress::clear_progress();
			
			$prefix = $this->input->get_var('backup','get',PLIB_Input::STRING);
			$backup = $this->backups->get_backup($prefix);
			if($backup == null)
			{
				$this->msgs->add_error($this->locale->lang('invalid_parameters'));
				return;
			}
			
			$_SESSION['BS_restore'] = array();
			$_SESSION['BS_restore']['prefix'] = $prefix;
			$_SESSION['BS_restore']['file'] = 0;
			$_SESSION['BS_restore']['total'] = $backup->files;
		}
	}
	
	public function get_total_operations()
	{
		return $_SESSION['BS_restore']['total'];
	}

	public function run($pos,$ops)
	{
		// import file
		$filename = $this->_get_next_file($pos);
		if($filename != '')
		{
			$statements = PLIB_SQLParser::get_statements_from_File('backups/'.$filename);
			foreach($statements as $sql)
				$this->db->sql_qry($sql);
		}
		
		// are we finished?
		if($pos >= $_SESSION['BS_restore']['total'])
			unset($_SESSION['BS_restore']);
	}
	
	/**
	 * determines the next file to import
	 * 
	 * @param int $pos the current position
	 * @return string the next file
	 */
	private function _get_next_file($pos)
	{
		if($handle = @opendir('backups'))
		{
			if($pos == 0)
				return $_SESSION['BS_restore']['prefix'].'structure.sql';
		
			$files = array();
			while($file = readdir($handle))
			{
				if($file == '.' || $file == '..')
					continue;
				
				if(preg_match('/^'.preg_quote($_SESSION['BS_restore']['prefix'],'/').'data\d+\.sql$/',$file))
					$files[] = $file;
			}
			closedir($handle);
			
			asort($files);
			if(isset($files[$pos - 1]))
				return $files[$pos - 1];
		}
		
		return false;
	}

	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>