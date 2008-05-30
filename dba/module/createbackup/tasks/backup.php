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
final class BS_DBA_Module_CreateBackup_Tasks_Backup extends PLIB_FullObject
	implements PLIB_Progress_Task
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		if(!isset($_SESSION['BS_backup']))
		{
			$structure = $this->input->isset_var('structure','post');
			$data = $this->input->isset_var('data','post');
			$tables = $this->input->get_var('tables','post');
			$prefix = $this->input->get_var('prefix','post',PLIB_Input::STRING);
			
			if(!$data && !$tables)
			{
				$this->_report_error();
				return;
			}
			
			$_SESSION['BS_backup'] = array();
			$_SESSION['BS_backup']['tables'] = $tables;
			$_SESSION['BS_backup']['prefix'] = $prefix;
			$_SESSION['BS_backup']['data'] = $data ? 1 : 0;
			$_SESSION['BS_backup']['structure'] = $structure ? 1 : 0;
			$_SESSION['BS_backup']['file'] = 1;
			$_SESSION['BS_backup']['total_files'] = 0;
			$_SESSION['BS_backup']['backup_size'] = 0;
		}
	}
	
	public function get_total_operations()
	{
		list($total,) = $this->_count_rows();
		return $total;
	}

	public function run($pos,$ops)
	{
		$len = count($_SESSION['BS_backup']['tables']);

		// write db-structure to file?
		if($pos == 0 && $_SESSION['BS_backup']['structure'])
		{
			// the structure should not be so much...therefore we store it in a single file
			$content = "";
			for($x = 0;$x < $len;$x++)
			{
				$content .= '# ------------------ Structure of '.$_SESSION['BS_backup']['tables'][$x];
				$content .= " ------------------".BS_DBA_LINE_WRAP;
				$content .= 'DROP TABLE IF EXISTS '.$_SESSION['BS_backup']['tables'][$x].";".BS_DBA_LINE_WRAP;
				
				$res = $this->db->sql_fetch_array($this->db->sql_qry(
					'SHOW CREATE TABLE '.$_SESSION['BS_backup']['tables'][$x]
				));
				$create_syntax = $res[1];
				$create_syntax = str_replace("\r\n",BS_DBA_LINE_WRAP,$create_syntax);
				$create_syntax = str_replace("\n",BS_DBA_LINE_WRAP,$create_syntax);
				$create_syntax = str_replace("\r",BS_DBA_LINE_WRAP,$create_syntax);
				if($this->db->get_server_version() < '4.1')
					$create_syntax = preg_replace('/\) ENGINE=.*/',') TYPE=MyISAM;',$create_syntax);
				
				$create_syntax = trim($create_syntax);
				if(PLIB_String::substr($create_syntax,-1,1) != ';')
					$create_syntax .= ';';
				
				$content .= $create_syntax.BS_DBA_LINE_WRAP.BS_DBA_LINE_WRAP;
			}
			
			// store to file
			$prefix = $_SESSION['BS_backup']['prefix'];
			$filename = PLIB_Path::inner().'dba/backups/'.$prefix.'structure.sql';
			if($this->_store_to_file($filename,$content))
			{
				$_SESSION['BS_backup']['total_files']++;
				$_SESSION['BS_backup']['backup_size'] += @filesize($filename);
			}
		}

		// backup the data
		$content = "";
		if($_SESSION['BS_backup']['data'])
		{
			$db = BS_DBA_Utils::get_instance()->get_selected_database();
			list($total,$num) = $this->_count_rows();
			$step_count = ceil($total / $ops) + 1;
			
			$lines = 0;
			$cpos = 0;
			for($x = 0;$x < $len;$x++)
			{
				if($pos > $cpos + $num[$_SESSION['BS_backup']['tables'][$x]])
				{
					$cpos += $num[$_SESSION['BS_backup']['tables'][$x]];
					continue;
				}
				
				// enough lines for this file?
				if($lines >= $ops)
					break;
				
				$start = ($pos + $lines) - $cpos;
				$length = $ops - $lines;
				
				if($start == 0)
				{
					$content .= '# ------------------ Data of '.$_SESSION['BS_backup']['tables'][$x];
					$content .= " ------------------".BS_DBA_LINE_WRAP.BS_DBA_LINE_WRAP;
				}
				
				$order = ' ORDER BY ';
				$fqry = mysql_list_fields($db,$_SESSION['BS_backup']['tables'][$x]);
				$field_num = $this->db->sql_num_fields($fqry);
				for($i = 0;$i < $field_num;$i++)
					$order .= '`'.$this->db->sql_field_name($fqry,$i).'` ASC,';
				$order = PLIB_String::substr($order,0,PLIB_String::strlen($order) - 1);
				
				$query = $this->db->sql_qry(
					'SELECT * FROM '.$_SESSION['BS_backup']['tables'][$x].' '.$order.'
					 LIMIT '.$start.','.$length
				);
				while($data = $this->db->sql_fetch_assoc($query))
				{
					$fields_tmp = '';
					$values_tmp = '';
					for($i = 0;$i < $field_num;$i++)
					{
						$field_name = $this->db->sql_field_name($query,$i);
						$fields_tmp .= '`'.$field_name.'`,';
						$value = addslashes($data[$field_name]);
						$value = str_replace("\r\n","\\r\\n",$value);
						$value = str_replace("\n","\\n",$value);
						$value = str_replace("\r","\\r",$value);
						$values_tmp .= "'".$value."',";
					}
	
					$fields = PLIB_String::substr($fields_tmp,0,PLIB_String::strlen($fields_tmp) - 1);
					$values = PLIB_String::substr($values_tmp,0,PLIB_String::strlen($values_tmp) - 1);
	
					$content .= 'INSERT INTO '.$_SESSION['BS_backup']['tables'][$x];
					$content .= ' ('.$fields.') VALUES ('.$values.");".BS_DBA_LINE_WRAP;
					$lines++;
				}
				$this->db->sql_free($query);
				
				$cpos += $num[$_SESSION['BS_backup']['tables'][$x]];
				
				if($lines > 0 && $lines < $ops)
					$content .= BS_DBA_LINE_WRAP.BS_DBA_LINE_WRAP;
			}
			
			// store the file
			$prefix = $_SESSION['BS_backup']['prefix'];
			$file = $_SESSION['BS_backup']['file'];
			$filename = PLIB_Path::inner().'dba/backups/'.$prefix.'data'.$file.'.sql';
			if($this->_store_to_file($filename,$content))
			{
				$_SESSION['BS_backup']['total_files']++;
				$_SESSION['BS_backup']['backup_size'] += @filesize($filename);
			}
			
			$_SESSION['BS_backup']['file']++;
		}
		
		// are we finished?
		if(!$_SESSION['BS_backup']['data'] || $_SESSION['BS_backup']['file'] >= $step_count)
		{
			$this->backups->add_backup(
				$_SESSION['BS_backup']['prefix'],$_SESSION['BS_backup']['total_files'],
				$_SESSION['BS_backup']['backup_size']
			);
			
			unset($_SESSION['BS_backup']);
		}
	}
	
	/**
	 * Counts the rows of the selected tables
	 *
	 * @return array an array of the form: <code>array(<total>,array(<table1> => <rows>,...))</code>
	 */
	private function _count_rows()
	{
		if(!$_SESSION['BS_backup']['data'])
			return array(0,array());
		
		$total = 0;
		$num = array();
		$db = BS_DBA_Utils::get_instance()->get_selected_database();
		$qry = $this->db->sql_qry('SHOW TABLE STATUS FROM `'.$db.'`');
		while($data = $this->db->sql_fetch_assoc($qry))
		{
			if(in_array($data['Name'],$_SESSION['BS_backup']['tables']))
			{
				$num[$data['Name']] = $data['Rows'];
				$total += $data['Rows'];
			}
		}
		$this->db->sql_free($qry);
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
		$res = PLIB_FileUtils::write($file,$content);
		if($res)
			@chmod($file,0666);
		return $res;
	}

	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>