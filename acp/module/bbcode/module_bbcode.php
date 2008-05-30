<?php
/**
 * Contains the bbcode module for the ACP
 * 
 * @version			$Id: module_bbcode.php 796 2008-05-29 18:23:27Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The bbcode-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_bbcode extends BS_ACP_SubModuleContainer
{
	public function __construct()
	{
		parent::__construct('bbcode',array('default','edit'),'default');
	}

	public function get_location()
	{
		$loc = array(
			$this->locale->lang('acpmod_bbcode') => $this->url->get_acpmod_url()
		);
		return array_merge($loc,$this->_sub->get_location());
	}
}
?>