<?php
/**
 * Contains the user module for the ACP
 * 
 * @version			$Id: module_user.php 705 2008-05-15 10:14:58Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The user-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_user extends BS_ACP_SubModuleContainer
{
	public function __construct()
	{
		// show edit-usergroups-page?
		$type = $this->input->get_var('action_type','post',PLIB_Input::STRING);
		if($type == 'edit_groups' && $this->input->get_var('delete','post') != null)
			$this->input->set_var('action','get','ugroups');
		
		parent::__construct('user',array('default','search','edit','ugroups','add'),'default');
	}

	public function get_location()
	{
		$loc = array(
			$this->locale->lang('acpmod_user') => $this->url->get_acpmod_url()
		);
		return array_merge($loc,$this->_sub->get_location());
	}
}
?>