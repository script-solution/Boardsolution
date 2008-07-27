<?php
/**
 * Contains the create-backup-module
 * 
 * @version			$Id$
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
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$msgs = PLIB_Props::get()->msgs();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$user = PLIB_Props::get()->user();
		$backups = PLIB_Props::get()->backups();

		$mode = $input->get_var('mode','get',PLIB_Input::STRING);
		if($mode == 'backup')
		{
			$this->_backup();
			return;
		}
		
		$user->delete_session_data('BS_backup');
		
		$gtables = $input->get_var('tables','get',PLIB_Input::STRING);
		$tables = PLIB_Array_Utils::advanced_explode(';',$gtables);
		$prefix = $input->get_var('prefix','post',PLIB_Input::STRING);
		
		if(!is_array($tables) || count($tables) == 0)
		{
			$this->report_error($locale->lang('no_tables_selected'));
			return;
		}
		
		// start backup?
		if($input->isset_var('submit','post'))
		{
			if(trim($prefix) != '' && $backups->get_backup($prefix) === null)
			{
				BS_DBA_Progress::clear_progress();
				$this->_backup();
				return;
			}
			
			$msgs->add_error($locale->lang('invalid_prefix'));
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
		
		$form = $this->request_formular();
		$form->set_condition($input->isset_var('prefix'));
		
		$tpl->add_variables(array(
			'target_url' => $url->get_url('createbackup','&amp;tables='.implode(';',$tables)),
			'hidden_tables' => $hidden_tables,
			'tables' => $sel_tables,
			'lang_structure' => $locale->lang('structure'),
			'lang_data' => $locale->lang('data')
		));
	}
	
	/**
	 * Starts / continues the backup
	 */
	private function _backup()
	{
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		new BS_DBA_Progress(
			$locale->lang('create_backup'),
			$locale->lang('backup_finished'),
			$url->get_url(0,'&mode=backup','&'),
			$url->get_url('backups'),
			new BS_DBA_Module_CreateBackup_Tasks_Backup()
		);
	}
}
?>