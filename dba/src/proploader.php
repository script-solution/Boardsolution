<?php
/**
 * Contains the property-loader-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * @see BS_PropLoader::sessions()
	 *
	 * @return PLIB_Session_Manager
	 */
	protected function sessions()
	{
		return new PLIB_Session_Manager(new PLIB_Session_Storage_PHP());
	}

	/**
	 * @see BS_PropLoader::user()
	 *
	 * @return PLIB_User_Current
	 */
	protected function user()
	{
		$user = new PLIB_User_Current(new BS_DBA_User_Storage_DBA());
		$user->set_use_cookies(false);
		return $user;
	}

	/**
	 * @see BS_PropLoader::url()
	 *
	 * @return BS_DBA_URL
	 */
	protected function url()
	{
		return BS_DBA_URL::get_instance();
	}

	/**
	 * @see BS_PropLoader::db()
	 *
	 * @return PLIB_MySQL
	 */
	protected function db()
	{
		$c = PLIB_MySQL::get_instance();
		$db = BS_DBA_Utils::get_instance()->get_selected_database();
		$c->connect(BS_MYSQL_HOST,BS_MYSQL_LOGIN,BS_MYSQL_PASSWORD,$db);
		$c->set_use_transactions(BS_USE_TRANSACTIONS);
		$c->init(BS_DB_CHARSET);
		$c->set_debugging_enabled(BS_DEBUG > 1);
		return $c;
	}

	/**
	 * @return BS_DBA_Backup_Manager the backup-manager
	 */
	protected function backups()
	{
		return new BS_DBA_Backup_Manager(PLIB_Path::server_app().'dba/backups/backups.txt');
	}
}
?>