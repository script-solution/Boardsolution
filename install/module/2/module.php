<?php
/**
 * Contains the type module for the installation
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
 * The type-module
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_2 extends BS_Install_Module
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
		$renderer->add_action(2,'forward');
		$renderer->get_action_performer()->perform_action_by_id(2);
	}

	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		$type = $user->get_session_data('install_type','full');
		$options = array(
			'full' => $locale->lang('fullinstall'),
			'update' => $locale->lang('update')
		);
		
		$this->request_formular();
		$tpl->add_variables(array(
			'type_options' => $options,
			'type_val' => $type
		));
	}
}
?>