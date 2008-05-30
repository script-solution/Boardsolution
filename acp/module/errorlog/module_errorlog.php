<?php
/**
 * Contains the error-log module for the ACP
 * 
 * @version			$Id: module_errorlog.php 705 2008-05-15 10:14:58Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The error-log-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_errorlog extends BS_ACP_SubModuleContainer
{
	public function __construct()
	{
		parent::__construct('errorlog',array('default','sendform'),'default');
	}

	public function get_location()
	{
		$loc = array(
			$this->locale->lang('acpmod_errorlog') => $this->url->get_acpmod_url()
		);
		return array_merge($loc,$this->_sub->get_location());
	}
}
?>