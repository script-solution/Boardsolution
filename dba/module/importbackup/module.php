<?php
/**
 * Contains the import-backup-module
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
 * The module to import a backup
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_importbackup extends BS_DBA_Module
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
		$renderer->add_action(BS_DBA_ACTION_IMPORT_BACKUP,'import');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();

		$user->delete_session_data('BS_restore');
		$user->delete_session_data('BS_backup');
		
		$this->request_formular();
		$tpl->add_variables(array(
			'action_type' => BS_DBA_ACTION_IMPORT_BACKUP
		));
	}
}
?>