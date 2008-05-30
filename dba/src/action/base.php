<?php
/**
 * Contains the action-base-class
 *
 * @version			$Id: base.php 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	dba.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The action-base class for all actions in the dbbackup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_DBA_Action_Base extends PLIB_Actions_Base
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// by default we don't want to redirect
		$this->set_redirect(false);
	}
}
?>