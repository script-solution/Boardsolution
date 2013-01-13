<?php
/**
 * Contains the acp-base-document-class
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
 * The base-document for all ACP-documents
 *
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_Document extends BS_Document
{
	/**
	 * @see FWS_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
	{
		// set a fix path to the acp-templates
		$tpl = FWS_Props::get()->tpl();
		$tpl->set_path('acp/templates/');
		$tpl->add_allowed_method('gurl','simple_acp_url');
		
		$locale = FWS_Props::get()->locale();
		$locale->add_language_file('admin');
		
		BS_URL::set_append_extern_vars(false);
		
		parent::prepare_rendering();
	}
}
?>