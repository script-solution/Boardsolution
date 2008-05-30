<?php
/**
 * Contains the acpaccess module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The acpaccess-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_acpaccess extends BS_ACP_SubModuleContainer
{
	public function __construct()
	{
		parent::__construct('acpaccess',array('default','module','client'),'default');
	}

	public function get_location()
	{
		$loc = array(
			$this->locale->lang('acpmod_acpaccess') => $this->url->get_acpmod_url()
		);
		return array_merge($loc,$this->_sub->get_location());
	}
}
?>