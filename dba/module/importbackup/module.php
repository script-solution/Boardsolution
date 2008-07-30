<?php
/**
 * Contains the import-backup-module
 * 
 * @version			$Id: module_importbackup.php 43 2008-07-30 10:47:55Z nasmussen $
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_DBA_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_DBA_ACTION_IMPORT_BACKUP,'import');
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$tpl = PLIB_Props::get()->tpl();
		$user = PLIB_Props::get()->user();

		$user->delete_session_data('BS_restore');
		$user->delete_session_data('BS_backup');
		
		$this->request_formular();
		$tpl->add_variables(array(
			'action_type' => BS_DBA_ACTION_IMPORT_BACKUP
		));
	}
}
?>