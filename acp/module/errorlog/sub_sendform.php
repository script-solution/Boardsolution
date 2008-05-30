<?php
/**
 * Contains the sendform-submodule for errorlog
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The sendform sub-module for the errorlog-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_errorlog_sendform extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_SEND_ERRORS => 'send'
		);
	}
	
	public function run()
	{
		$this->tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_SEND_ERRORS
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('send_errors') => $this->url->get_acpmod_url(0,'&amp;action=sendform')
		);
	}
}
?>