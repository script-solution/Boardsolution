<?php
/**
 * Contains the show-backups-module
 * 
 * @version			$Id: module_backups.php 43 2008-07-30 10:47:55Z nasmussen $
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_DBA_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_DBA_ACTION_DELETE_BACKUPS,'delete');
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();
		$tpl = PLIB_Props::get()->tpl();
		$backups = PLIB_Props::get()->backups();
		$user = PLIB_Props::get()->user();
		
		$user->delete_session_data('BS_restore');
		$user->delete_session_data('BS_backup');
		
		$mode = $input->get_var('mode','get',PLIB_Input::STRING);
		
		// show delete message?
		if(($delete = $input->get_var('delete','post')) != null)
		{
			$message = sprintf($locale->lang('delete_backups'),'"'.implode('","',$delete).'"');
			$yes_url = $url->get_url(
				0,'&amp;at='.BS_DBA_ACTION_DELETE_BACKUPS.'&amp;backups='.implode(',',$delete)
			);
			$no_url = $url->get_url(0);
			
			$functions->add_delete_message($message,$yes_url,$no_url,'');
		}
		
		// show restore-message?
		if($mode == 'qrestore')
		{
			$selected_db = BS_DBA_Utils::get_instance()->get_selected_database();
			$backup = $input->get_var('backup','get',PLIB_Input::STRING);
			$message = sprintf($locale->lang('restore_backup_question'),$backup,$selected_db);
			$yes_url = $url->get_url('restorebackup','&amp;backup='.$backup);
			$no_url = $url->get_url(0);
			
			$functions->add_delete_message($message,$yes_url,$no_url,'');
		}
		
		$tpl_backups = array();
		foreach($backups->get_backups() as $backup)
		{
			$tpl_backups[] = array(
				'backup_prefix' => $backup->prefix,
				'date' => PLIB_Date::get_date($backup->date),
				'files' => $backup->files,
				'size' => PLIB_StringHelper::get_formated_data_size((int)$backup->size,BS_DBA_LANGUAGE),
				'restore_url' => $url->get_url(0,'&amp;mode=qrestore&amp;backup='.$backup->prefix)
			);
		}
		
		$tpl->add_array('backups',$tpl_backups);
	}
}
?>