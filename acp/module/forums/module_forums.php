<?php
/**
 * Contains the forums module for the ACP
 * 
 * @version			$Id: module_forums.php 709 2008-05-15 14:35:04Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The forums-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_forums extends BS_ACP_SubModuleContainer
{
	public function __construct()
	{
		parent::__construct('forums',array('default','edit'),'default');
	}

	public function get_location()
	{
		$loc = array(
			$this->locale->lang('acpmod_forums') => $this->url->get_acpmod_url()
		);
		return array_merge($loc,$this->_sub->get_location());
	}
}
?>