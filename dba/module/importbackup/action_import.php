<?php
/**
 * Contains the import-importbackup-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The import-importbackup-action
 *
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Action_importbackup_import extends BS_DBA_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();

		$prefix = $input->get_var('prefix','post',PLIB_Input::STRING);
		if($prefix === null || PLIB_String::strlen($prefix) == 0)
			return 'invalid_prefix';
		
		$count = 0;
		$size = 0;
		
		// read all files to see if there are any with the given prefix
		if($handle = @opendir(PLIB_Path::server_app().'dba/backups/'))
		{
			while($file = readdir($handle))
			{
				if($file == '.' || $file == '..')
					continue;
				
				if(PLIB_String::starts_with($file,$prefix))
				{
					$count++;
					$size += filesize(PLIB_Path::server_app().'dba/backups/'.$file);
				}
			}
			closedir($handle);
		}
		
		if($count == 0)
			return 'import_backup_failed';
		
		if(!$this->backups->add_backup($prefix,$count,$size))
			return 'invalid_prefix';
		
		$this->set_success_msg($locale->lang('import_backup_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>