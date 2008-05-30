<?php
/**
 * Contains the import-backup-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module to import a backup
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_importbackup extends BS_DBA_Module
{
	public function get_actions()
	{
		return array(
			BS_DBA_ACTION_IMPORT_BACKUP => 'import'
		);
	}
	
	public function run()
	{
		unset($_SESSION['BS_restore']);
		unset($_SESSION['BS_backup']);
		
		$this->_request_formular();
		$this->tpl->add_variables(array(
			'action_type' => BS_DBA_ACTION_IMPORT_BACKUP
		));
	}
}
?>