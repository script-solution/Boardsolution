<?php
/**
 * Contains the backup-task-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The backup-task
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_CreateBackup_Tasks_Backup extends FWS_Object implements FWS_Progress_Task
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();

		parent::__construct();
		
		if($user->get_session_data('BS_backup') === false)
		{
			$structure = $input->isset_var('structure','post');
			$data = $input->isset_var('data','post');
			$tables = $input->get_var('tables','post');
			$prefix = $input->get_var('prefix','post',FWS_Input::STRING);
			
			if(!$data && !$tables)
			{
				$msgs = FWS_Props::get()->msgs();
				$doc = FWS_Props::get()->doc();
				$msgs->add_error($locale->lang('no_tables_selected'));
				$doc->get_module()->set_error();
				return;
			}
			
			$user->set_session_data('BS_backup',array(
				'tables' => $tables,
				'prefix' => $prefix,
				'data' => $data ? 1 : 0,
				'structure' => $structure ? 1 : 0,
				'file' => 1,
				'total_files' => 0,
				'backup_size' => 0
			));
		}
	}
	
	public function get_total_operations()
	{
		list($total,) = $this->_count_rows();
		return $total;
	}

	public function run($pos,$ops)
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		
		$data = $user->get_session_data('BS_backup');
		
		$len = count($data['tables']);

		// write db-structure to file?
		if($pos == 0 && $data['structure'])
		{
			// the structure should not be so much...therefore we store it in a single file
			$content = "";
			for($x = 0;$x < $len;$x++)
			{
				$content .= '# ------------------ Structure of '.$data['tables'][$x];
				$content .= " ------------------".BS_DBA_LINE_WRAP;
				$content .= 'DROP TABLE IF EXISTS '.$data['tables'][$x].";".BS_DBA_LINE_WRAP;
				
				$res = $db->get_row('SHOW CREATE TABLE '.$data['tables'][$x]);
				$create_syntax = $res['Create Table'];
				$create_syntax = str_replace("\r\n",BS_DBA_LINE_WRAP,$create_syntax);
				$create_syntax = str_replace("\n",BS_DBA_LINE_WRAP,$create_syntax);
				$create_syntax = str_replace("\r",BS_DBA_LINE_WRAP,$create_syntax);
				if($db->get_server_version() < '4.1')
					$create_syntax = preg_replace('/\) ENGINE=.*/',') TYPE=MyISAM;',$create_syntax);
				
				$create_syntax = trim($create_syntax);
				if(FWS_String::substr($create_syntax,-1,1) != ';')
					$create_syntax .= ';';
				
				$content .= $create_syntax.BS_DBA_LINE_WRAP.BS_DBA_LINE_WRAP;
			}
			
			// store to file
			$prefix = $data['prefix'];
			$filename = FWS_Path::server_app().'dba/backups/'.$prefix.'structure.sql';
			if($this->_store_to_file($filename,$content))
			{
				$data['total_files']++;
				$data['backup_size'] += @filesize($filename);
			}
		}

		// backup the data
		$content = "";
		if($data['data'])
		{
			$dbname = BS_DBA_Utils::get_instance()->get_selected_database();
			list($total,$num) = $this->_count_rows();
			$step_count = ceil($total / $ops) + 1;
			
			$lines = 0;
			$cpos = 0;
			for($x = 0;$x < $len;$x++)
			{
				if($pos > $cpos + $num[$data['tables'][$x]])
				{
					$cpos += $num[$data['tables'][$x]];
					continue;
				}
				
				// enough lines for this file?
				if($lines >= $ops)
					break;
				
				$start = max(0,($pos + $lines) - $cpos);
				$length = $ops - $lines;
				
				if($start == 0)
				{
					$content .= '# ------------------ Data of '.$data['tables'][$x];
					$content .= " ------------------".BS_DBA_LINE_WRAP.BS_DBA_LINE_WRAP;
				}
				
				$order = ' ORDER BY ';
				$set = $db->execute('SHOW COLUMNS FROM '.$data['tables'][$x]);
				foreach($set as $row)
					$order .= '`'.$row['Field'].'` ASC,';
				$order = FWS_String::substr($order,0,FWS_String::strlen($order) - 1);
				
				$set = $db->execute(
					'SELECT * FROM '.$data['tables'][$x].' '.$order.'
					 LIMIT '.$start.','.$length
				);
				foreach($set as $row)
				{
					$fields_tmp = '';
					$values_tmp = '';
					for($i = 0;$i < $set->get_field_count();$i++)
					{
						$field_name = $set->get_field_name($i);
						$fields_tmp .= '`'.$field_name.'`,';
						$value = addslashes($row[$field_name]);
						$value = str_replace("\r\n","\\r\\n",$value);
						$value = str_replace("\n","\\n",$value);
						$value = str_replace("\r","\\r",$value);
						$values_tmp .= "'".$value."',";
					}
	
					$fields = FWS_String::substr($fields_tmp,0,FWS_String::strlen($fields_tmp) - 1);
					$values = FWS_String::substr($values_tmp,0,FWS_String::strlen($values_tmp) - 1);
	
					$content .= 'INSERT INTO '.$data['tables'][$x];
					$content .= ' ('.$fields.') VALUES ('.$values.");".BS_DBA_LINE_WRAP;
					$lines++;
				}
				
				$cpos += $num[$data['tables'][$x]];
				
				if($lines > 0 && $lines < $ops)
					$content .= BS_DBA_LINE_WRAP.BS_DBA_LINE_WRAP;
			}
			
			// store the file
			$prefix = $data['prefix'];
			$file = $data['file'];
			$filename = FWS_Path::server_app().'dba/backups/'.$prefix.'data'.$file.'.sql';
			if($this->_store_to_file($filename,$content))
			{
				$data['total_files']++;
				$data['backup_size'] += @filesize($filename);
			}
			
			$data['file']++;
		}
		
		$user->set_session_data('BS_backup',$data);
		
		// are we finished?
		if(!$data['data'] || $data['file'] >= $step_count)
		{
			$backups = FWS_Props::get()->backups();
			$backups->add_backup(
				$data['prefix'],$data['total_files'],
				$data['backup_size']
			);
			
			$user->delete_session_data('BS_backup');
		}
	}
	
	/**
	 * Counts the rows of the selected tables
	 *
	 * @return array an array of the form: <code>array(<total>,array(<table1> => <rows>,...))</code>
	 */
	private function _count_rows()
	{
		$db = FWS_Props::get()->db();
		$user = FWS_Props::get()->user();
		
		$data = $user->get_session_data('BS_backup');
		if(!$data['data'])
			return array(0,array());
		
		$total = 0;
		$num = array();
		$dbname = BS_DBA_Utils::get_instance()->get_selected_database();
		foreach($db->get_rows('SHOW TABLE STATUS FROM `'.$dbname.'`') as $row)
		{
			if(in_array($row['Name'],$data['tables']))
			{
				// $row['Rows'] may be approximated with InnoDB
				$num[$row['Name']] = $db->get_row_count($row['Name'],'*','');
				$total += $num[$row['Name']];
			}
		}
		return array($total,$num);
	}
	
	/**
	 * Stores the given string to file
	 * 
	 * @param string $file the file to write to
	 * @param string $content the string to write
	 * @return boolean true if successfull
	 */
	private function _store_to_file($file,$content)
	{
		$res = FWS_FileUtils::write($file,$content);
		if($res)
			@chmod($file,0666);
		return $res;
	}

	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>