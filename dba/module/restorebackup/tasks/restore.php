<?php
/**
 * Contains the restore-task-class
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
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
		if($filename)
		{
			$statements = FWS_SQLParser::get_statements_from_file('backups/'.$filename);
			foreach($statements as $sql)
				$db->execute($sql);
		}
		
		// are we finished?
		if($pos >= $data['total'])
			$user->delete_session_data('BS_restore');
	}
	
	/**
	 * determines the next file to import
	 * 
	 * @param int $pos the current position
	 * @return string|bool the next file or false if we're done
	 */
	private function _get_next_file($pos)
	{
		$user = FWS_Props::get()->user();
		$data = $user->get_session_data('BS_restore');
		
		if($handle = @opendir('backups'))
		{
			if($pos == 0 && is_file('backups/'.$data['prefix'].'structure.sql'))
				return $data['prefix'].'structure.sql';
			// pretend that we had a structure.sql
			if(!is_file('backups/'.$data['prefix'].'structure.sql'))
				$pos++;
		
			$files = array();
			while($file = readdir($handle))
			{
				if($file == '.' || $file == '..')
					continue;
				
				if(preg_match('/^'.preg_quote($data['prefix'],'/').'data(\d+)\.sql$/',$file,$match))
					$files[$file] = $match[1];
			}
			closedir($handle);
			
			asort($files);
			$files = array_flip($files);
			if(isset($files[$pos]))
				return $files[$pos];
		}
		
		return false;
	}

	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>