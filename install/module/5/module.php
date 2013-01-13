<?php
/**
 * Contains the process module for the installation
 * 
 * @package			Boardsolution
 * @subpackage	install.module
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
 * The process-module
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_5 extends BS_Install_Module
{
	/**
	 * @see FWS_Module::init()
	 *
	 * @param BS_Install_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(5,'forward');
		$renderer->get_action_performer()->perform_action_by_id(5);
		
		$this->connect_to_db();
	}

	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();

		$type = $user->get_session_data('install_type','full');
		if($type == 'full')
			$install = new BS_Install_Module_5_SQL_Full();
		else
			$install = new BS_Install_Module_5_SQL_Update();
		
		$install->start();
		
		$tpl->add_variables(array(
			'log' => $install->get_log()
		));
	}
}
?>