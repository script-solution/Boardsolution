<?php
/**
 * Contains the correctmsgs module for the ACP
 * 
 * @version			$Id: module_correctmsgs.php 705 2008-05-15 10:14:58Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The correctmsgs-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_correctmsgs extends BS_ACP_SubModuleContainer
{
	public function __construct()
	{
		parent::__construct('correctmsgs',array('default','cycle'),'default');
	}

	public function get_location()
	{
		$loc = array(
			$this->locale->lang('acpmod_correctmsgs') => $this->url->get_acpmod_url()
		);
		return array_merge($loc,$this->_sub->get_location());
	}
}
?>