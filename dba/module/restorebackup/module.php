<?php
/**
 * Contains the restore-backup-module
 * 
 * @version			$Id: module_restorebackup.php 39 2008-07-27 18:24:44Z nasmussen $
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module to restore a backup
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_restorebackup extends BS_DBA_Module
{
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		new BS_DBA_Progress(
			$locale->lang('restore_backup'),
			$locale->lang('restore_finished'),
			$url->get_url(0,'','&'),
			$url->get_url('backups'),
			new BS_DBA_Module_RestoreBackup_Tasks_Restore(),
			1
		);
	}
}
?>