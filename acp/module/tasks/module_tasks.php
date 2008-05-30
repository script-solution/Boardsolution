<?php
/**
 * Contains the tasks module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The tasks-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_tasks extends BS_ACP_SubModuleContainer
{
	public function __construct()
	{
		parent::__construct('tasks',array('default','edit','add'),'default');
	}

	public function get_location()
	{
		$loc = array(
			$this->locale->lang('acpmod_tasks') => $this->url->get_acpmod_url()
		);
		return array_merge($loc,$this->_sub->get_location());
	}
}
?>