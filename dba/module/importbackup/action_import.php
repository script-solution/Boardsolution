<?php
/**
 * Contains the import-importbackup-action
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
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$backups = FWS_Props::get()->backups();

		$prefix = $input->get_var('prefix','post',FWS_Input::STRING);
		if($prefix === null || FWS_String::strlen($prefix) == 0)
			return 'invalid_prefix';
		
		$count = 0;
		$size = 0;
		
		// read all files to see if there are any with the given prefix
		if($handle = @opendir(FWS_Path::server_app().'dba/backups/'))
		{
			while($file = readdir($handle))
			{
				if($file == '.' || $file == '..')
					continue;
				
				if(FWS_String::starts_with($file,$prefix))
				{
					$count++;
					$size += filesize(FWS_Path::server_app().'dba/backups/'.$file);
				}
			}
			closedir($handle);
		}
		
		if($count == 0)
			return 'import_backup_failed';
		
		if(!$backups->add_backup($prefix,$count,$size))
			return 'invalid_prefix';
		
		$this->set_success_msg($locale->lang('import_backup_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>