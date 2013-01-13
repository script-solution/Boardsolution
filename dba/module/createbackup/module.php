<?php
/**
 * Contains the create-backup-module
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
 * The module to create a backup
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_createbackup extends BS_DBA_Module
{
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$msgs = FWS_Props::get()->msgs();
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
		$backups = FWS_Props::get()->backups();

		$mode = $input->get_var('mode','get',FWS_Input::STRING);
		if($mode == 'backup')
		{
			$this->_backup();
			return;
		}
		
		$user->delete_session_data('BS_backup');
		
		$gtables = $input->get_var('tables','get',FWS_Input::STRING);
		$tables = FWS_Array_Utils::advanced_explode(';',$gtables);
		$prefix = $input->get_var('prefix','post',FWS_Input::STRING);
		
		if(!is_array($tables) || count($tables) == 0)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('no_tables_selected'));
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
			'target_url' => BS_DBA_URL::build_url('createbackup','&amp;tables='.implode(';',$tables)),
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
		$locale = FWS_Props::get()->locale();
		new BS_DBA_Progress(
			$locale->lang('create_backup'),
			$locale->lang('backup_finished'),
			BS_DBA_URL::build_url(0,'&mode=backup','&'),
			BS_DBA_URL::build_url('backups'),
			new BS_DBA_Module_CreateBackup_Tasks_Backup()
		);
	}
}
?>