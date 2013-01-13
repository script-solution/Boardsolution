<?php
/**
 * Contains the show-backups-module
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
 * The module to show all backups
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_backups extends BS_DBA_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_DBA_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_DBA_ACTION_DELETE_BACKUPS,'delete');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		$backups = FWS_Props::get()->backups();
		$user = FWS_Props::get()->user();
		
		$user->delete_session_data('BS_restore');
		$user->delete_session_data('BS_backup');
		
		$mode = $input->get_var('mode','get',FWS_Input::STRING);
		
		// show delete message?
		if(($delete = $input->get_var('delete','post')) != null)
		{
			$message = sprintf($locale->lang('delete_backups'),'"'.implode('","',$delete).'"');
			$yes_url = BS_DBA_URL::build_url(
				0,'&amp;aid='.BS_DBA_ACTION_DELETE_BACKUPS.'&amp;backups='.implode(',',$delete)
			);
			$no_url = BS_DBA_URL::build_url(0);
			
			$functions->add_delete_message($message,$yes_url,$no_url,'');
		}
		
		// show restore-message?
		if($mode == 'qrestore')
		{
			$selected_db = BS_DBA_Utils::get_instance()->get_selected_database();
			$backup = $input->get_var('backup','get',FWS_Input::STRING);
			$message = sprintf($locale->lang('restore_backup_question'),$backup,$selected_db);
			$yes_url = BS_DBA_URL::build_url('restorebackup','&amp;backup='.$backup);
			$no_url = BS_DBA_URL::build_url(0);
			
			$functions->add_delete_message($message,$yes_url,$no_url,'');
		}
		
		$tpl_backups = array();
		foreach($backups->get_backups() as $backup)
		{
			$tpl_backups[] = array(
				'backup_prefix' => $backup->prefix,
				'date' => FWS_Date::get_date($backup->date),
				'files' => $backup->files,
				'size' => FWS_StringHelper::get_formated_data_size((int)$backup->size,BS_DBA_LANGUAGE),
				'restore_url' => BS_DBA_URL::build_url(0,'&amp;mode=qrestore&amp;backup='.$backup->prefix)
			);
		}
		
		$tpl->add_variable_ref('backups',$tpl_backups);
	}
}
?>