<?php
/**
 * Contains the default-submodule for miscellaneous
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the miscellaneous-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_miscellaneous_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array();
	}
	
	public function run()
	{
		// we have to clear the position here to ensure that we will start again
		// if the last progress hasn't be cleaned up, however.
		$storage = new PLIB_Progress_Storage_Session('misc_');
		$storage->clear();
		
		$tasks = BS_ACP_Module_miscellaneous::get_tasks();
		$this->tpl->add_array('tasks',$tasks);
	}
	
	public function get_location()
	{
		return array();
	}
}
?>