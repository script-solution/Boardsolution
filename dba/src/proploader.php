<?php
/**
 * Contains the property-loader-class
 * 
 * @package			Boardsolution
 * @subpackage	dba.src
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
 * The property-loader for the db-backup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_PropLoader extends BS_PropLoader
{
	/**
	 * Loads the document
	 *
	 * @return BS_DBA_Document the document
	 */
	protected function doc()
	{
		return new BS_DBA_Document();
	}

	/**
	 * @return FWS_Cookies the property
	 */
	protected function cookies()
	{
		// use a different cookie-prefix, to prevent conflicts
		$c = new FWS_Cookies(BS_COOKIE_PREFIX.'dba');
		$c->set_lifetime(BS_COOKIE_LIFETIME);
		return $c;
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
	 * @see BS_PropLoader::sessions()
	 *
	 * @return FWS_Session_Manager
	 */
	protected function sessions()
	{
		return new FWS_Session_Manager(new FWS_Session_Storage_PHP());
	}

	/**
	 * @see BS_PropLoader::user()
	 *
	 * @return FWS_User_Current
	 */
	protected function user()
	{
		$user = new FWS_User_Current(new BS_DBA_User_Storage_DBA());
		$user->set_use_cookies(false);
		return $user;
	}

	/**
	 * @see BS_PropLoader::db()
	 *
	 * @return FWS_DB_MySQL_Connection
	 */
	protected function db()
	{
		$functions = FWS_Props::get()->functions();
		$db = BS_DBA_Utils::get_instance()->get_selected_database();
		
		return $functions->connect_to_db(BS_MYSQL_HOST, BS_MYSQL_LOGIN, BS_MYSQL_PASSWORD, $db);
	}

	/**
	 * @return BS_DBA_Backup_Manager the backup-manager
	 */
	protected function backups()
	{
		return new BS_DBA_Backup_Manager(FWS_Path::server_app().'dba/backups/backups.txt');
	}
}
?>