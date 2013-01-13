<?php
/**
 * Contains the restore-backup-module
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
 * The module to restore a backup
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_restorebackup extends BS_DBA_Module
{
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$locale = FWS_Props::get()->locale();
		new BS_DBA_Progress(
			$locale->lang('restore_backup'),
			$locale->lang('restore_finished'),
			BS_DBA_URL::build_url(0,'','&'),
			BS_DBA_URL::build_url('backups'),
			new BS_DBA_Module_RestoreBackup_Tasks_Restore(),
			1
		);
	}
}
?>