<?php
/**
 * Contains the base-action-class for the ACP
 *
 * @version			$Id: base.php 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-action-class for the ACP of Boardsolution
 *
 * @package			Boardsolution
 * @subpackage	acp.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_Action_Base extends PLIB_Actions_Base
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