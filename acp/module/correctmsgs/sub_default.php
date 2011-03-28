<?php
/**
 * Contains the default-submodule for correctmsgs
 * 
 * @version			$Id$
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
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();

		$user->delete_session_data('im_data');
		$incorrect = BS_ACP_Module_CorrectMsgs_Helper::get_incorrect_messages();
		
		$url = BS_URL::get_acpsub_url(0,'cycle');
		$url->set('pos',0);
		
		$tpl->add_variables(array(
			'target' => $url->to_url(),
			'incorrect_messages' => sprintf($locale->lang('incorrect_messages'),count($incorrect)),
			'incorrect_num' => count($incorrect)
		));
	}
}
?>