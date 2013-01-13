<?php
/**
 * Contains the ACP-module-base-class
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
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
 * The module-base class for all ACP-modules
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_Module extends FWS_Module
{
	/**
	 * @see FWS_Module::request_formular()
	 *
	 * @return BS_HTML_Formular
	 */
	protected final function request_formular()
	{
		$tpl = FWS_Props::get()->tpl();

		$form = new BS_HTML_Formular(false,false);
		$tpl->add_variable_ref('form',$form);
		$tpl->add_allowed_method('form','*');
		return $form;
	}
}
?>