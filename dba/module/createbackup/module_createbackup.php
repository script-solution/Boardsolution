<?php
/**
 * Contains the create-backup-module
 * 
 * @version			$Id: module_createbackup.php 685 2008-05-10 16:03:25Z nasmussen $
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module to create a backup
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_createbackup extends BS_DBA_Module
{
	public function run()
	{
		$mode = $this->input->get_var('mode','get',PLIB_Input::STRING);
		if($mode == 'backup')
		{
			$this->_backup();
			return;
		}
		
		unset($_SESSION['BS_backup']);
		
		$gtables = $this->input->get_var('tables','get',PLIB_Input::STRING);
		$tables = PLIB_Array_Utils::advanced_explode(';',$gtables);
		$prefix = $this->input->get_var('prefix','post',PLIB_Input::STRING);
		
		if(!is_array($tables) || count($tables) == 0)
		{
			$this->_report_error($this->locale->lang('no_tables_selected'));
			return;
		}
		
		// start backup?
		if($this->input->isset_var('submit','post'))
		{
			if(trim($prefix) != '' && $this->backups->get_backup($prefix) === null)
			{
				BS_DBA_Progress::clear_progress();
				$this->_backup();
				return;
			}
			
			$this->msgs->add_error($this->locale->lang('invalid_prefix'));
		}
		
		$database = BS_DBA_Utils::get_instance()->get_selected_database();
		$hidden_tables = '';
		$sel_tables = '<ul>'."\n";
		foreach($tables as $table)
		{
			$hidden_tables .= '<input type="hidden" name="tables[]" value="'.$table.'" />'."\n";
			$sel_tables .= '	<li>'.$database.' . '.$table.'</li>'."\n";
		}
		$sel_tables .= '</ul>';
		
		$form = $this->_request_formular();
		$form->set_condition($this->input->isset_var('prefix'));
		
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url('createbackup','&amp;tables='.implode(';',$tables)),
			'hidden_tables' => $hidden_tables,
			'tables' => $sel_tables,
			'lang_structure' => $this->locale->lang('structure'),
			'lang_data' => $this->locale->lang('data')
		));
	}
	
	/**
	 * Starts / continues the backup
	 */
	private function _backup()
	{
		new BS_DBA_Progress(
			$this->locale->lang('create_backup'),
			$this->locale->lang('backup_finished'),
			$this->url->get_url(0,'&mode=backup','&'),
			$this->url->get_url('backups'),
			new BS_DBA_Module_CreateBackup_Tasks_Backup()
		);
	}
}
?>