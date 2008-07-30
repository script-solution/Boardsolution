<?php
/**
 * Contains the restore-task-class
 *
 * @version			$Id$
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
final class BS_DBA_Module_RestoreBackup_Tasks_Restore extends FWS_Object
	implements FWS_Progress_Task
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$input = FWS_Props::get()->input();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$backups = FWS_Props::get()->backups();

		parent::__construct();
		
		// init session
		if($user->get_session_data('BS_restore') === false)
		{
			// ensure that we start a new progress
			BS_DBA_Progress::clear_progress();
			
			$prefix = $input->get_var('backup','get',FWS_Input::STRING);
			$backup = $backups->get_backup($prefix);
			if($backup == null)
			{
				$msgs->add_error($locale->lang('invalid_parameters'));
				return;
			}
			
			$user->set_session_data('BS_restore',array(
				'prefix' => $prefix,
				'file' => 0,
				'total' => $backup->files
			));
		}
	}
	
	public function get_total_operations()
	{
		$user = FWS_Props::get()->user();
		$data = $user->get_session_data('BS_restore');
		return $data['total'];
	}

	public function run($pos,$ops)
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		$data = $user->get_session_data('BS_restore');

		// import file
		$filename = $this->_get_next_file($pos);
		if($filename != '')
		{
			$statements = FWS_SQLParser::get_statements_from_File('backups/'.$filename);
			foreach($statements as $sql)
				$db->sql_qry($sql);
		}
		
		// are we finished?
		if($pos >= $data['total'])
			$user->delete_session_data('BS_restore');
	}
	
	/**
	 * determines the next file to import
	 * 
	 * @param int $pos the current position
	 * @return string the next file
	 */
	private function _get_next_file($pos)
	{
		$user = FWS_Props::get()->user();
		$data = $user->get_session_data('BS_restore');
		
		if($handle = @opendir('backups'))
		{
			if($pos == 0)
				return $data['prefix'].'structure.sql';
		
			$files = array();
			while($file = readdir($handle))
			{
				if($file == '.' || $file == '..')
					continue;
				
				if(preg_match('/^'.preg_quote($data['prefix'],'/').'data\d+\.sql$/',$file))
					$files[] = $file;
			}
			closedir($handle);
			
			asort($files);
			if(isset($files[$pos - 1]))
				return $files[$pos - 1];
		}
		
		return false;
	}

	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>