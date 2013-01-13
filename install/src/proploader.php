<?php
/**
 * Contains the property-loader-class
 * 
 * @package			Boardsolution
 * @subpackage	install.src
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
 * The property-loader for the install-script
 *
 * @package			Boardsolution
 * @subpackage	install.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_PropLoader extends BS_PropLoader
{
	/**
	 * @see BS_PropLoader::sessions()
	 *
	 * @return BS_Session_Manager
	 */
	protected function sessions()
	{
		return new FWS_Session_Manager(new FWS_Session_Storage_PHP(),true);
	}

	/**
	 * @see BS_PropLoader::user()
	 *
	 * @return BS_User_Current
	 */
	protected function user()
	{
		return new FWS_User_Current(new FWS_User_Storage_Empty());
	}
	
	/**
	 * Loads the document
	 *
	 * @return BS_Install_Document the document
	 */
	protected function doc()
	{
		return new BS_Install_Document();
	}
	
	/**
	 * @return FWS_Template_Handler the template-handler
	 */
	protected function tpl()
	{
		$tpl = parent::tpl();
		
		// replace the URL-class
		$url = new BS_DBA_URL();
		$tpl->add_global_ref('gurl',$url);
		$tpl->remove_allowed_methods('gurl');
		$tpl->add_allowed_method('gurl','build_url');
		
		return $tpl;
	}

	/**
	 * @see BS_PropLoader::db()
	 *
	 * @return FWS_DB_MySQL_Connection
	 */
	protected function db()
	{
		return new FWS_DB_MySQL_Connection();
	}
}
?>