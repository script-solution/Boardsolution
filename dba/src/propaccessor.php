<?php
/**
 * Contains the property-accessor-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The property-accessor for the db-backup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_PropAccessor extends BS_PropAccessor
{
	/**
	 * @see BS_PropAccessor::doc()
	 *
	 * @return BS_DBA_Document
	 */
	public function doc()
	{
		return $this->get('doc');
	}

	/**
	 * @return BS_DBA_Backup_Manager the backup-manager
	 */
	public function backups()
	{
		return $this->get('backups');
	}
}
?>