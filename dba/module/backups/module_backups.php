<?php
/**
 * Contains the show-backups-module
 * 
 * @version			$Id: module_backups.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	public function get_actions()
	{
		return array(
			BS_DBA_ACTION_DELETE_BACKUPS => 'delete'
		);
	}
	
	public function run()
	{
		unset($_SESSION['BS_restore']);
		unset($_SESSION['BS_backup']);
		
		$mode = $this->input->get_var('mode','get',PLIB_Input::STRING);
		
		// show delete message?
		if(($delete = $this->input->get_var('delete','post')) != null)
		{
			$message = sprintf($this->locale->lang('delete_backups'),'"'.implode('","',$delete).'"');
			$yes_url = $this->url->get_url(
				0,'&amp;at='.BS_DBA_ACTION_DELETE_BACKUPS.'&amp;backups='.implode(',',$delete)
			);
			$no_url = $this->url->get_url(0);
			
			$this->functions->add_delete_message($message,$yes_url,$no_url,'');
		}
		
		// show restore-message?
		if($mode == 'qrestore')
		{
			$selected_db = BS_DBA_Utils::get_instance()->get_selected_database();
			$backup = $this->input->get_var('backup','get',PLIB_Input::STRING);
			$message = sprintf($this->locale->lang('restore_backup_question'),$backup,$selected_db);
			$yes_url = $this->url->get_url('restorebackup','&amp;backup='.$backup);
			$no_url = $this->url->get_url(0);
			
			$this->functions->add_delete_message($message,$yes_url,$no_url,'');
		}
		
		$tpl_backups = array();
		$backups = $this->backups->get_backups();
		foreach($backups as $backup)
		{
			$tpl_backups[] = array(
				'backup_prefix' => $backup->prefix,
				'date' => PLIB_Date::get_date($backup->date),
				'files' => $backup->files,
				'size' => PLIB_StringHelper::get_formated_data_size((int)$backup->size,BS_DBA_LANGUAGE),
				'restore_url' => $this->url->get_url(0,'&amp;mode=qrestore&amp;backup='.$backup->prefix)
			);
		}
		
		$this->tpl->add_array('backups',$tpl_backups);
	}
}
?>