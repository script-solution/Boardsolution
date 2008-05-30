<?php
/**
 * Contains the default-submodule for correctmsgs
 * 
 * @version			$Id: sub_default.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the correctmsgs-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_correctmsgs_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array();
	}
	
	public function run()
	{
		$helper = BS_ACP_Module_CorrectMsgs_Helper::get_instance();
		
		$this->user->delete_session_data('im_data');
		$incorrect = $helper->get_incorrect_messages();
		
		$this->tpl->add_variables(array(
			'target' => $this->url->get_acpmod_url(0,'&amp;action=cycle&amp;pos=0'),
			'incorrect_messages' => sprintf($this->locale->lang('incorrect_messages'),count($incorrect)),
			'incorrect_num' => count($incorrect)
		));
	}
	
	public function get_location()
	{
		return array();
	}
}
?>