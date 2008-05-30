<?php
/**
 * Contains the add-submodule for themes
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add sub-module for the themes-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_themes_add extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_ADD_THEME => 'add'
		);
	}
	
	public function run()
	{
		$this->_request_formular();
		$this->tpl->add_variables(array(
			'at_add' => BS_ACP_ACTION_ADD_THEME
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('add_theme') => $this->url->get_acpmod_url(0,'&amp;action=add')
		);
	}
}
?>