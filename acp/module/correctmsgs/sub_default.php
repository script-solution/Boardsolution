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
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$user = PLIB_Props::get()->user();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$locale = PLIB_Props::get()->locale();

		$helper = BS_ACP_Module_CorrectMsgs_Helper::get_instance();
		
		$user->delete_session_data('im_data');
		$incorrect = $helper->get_incorrect_messages();
		
		$tpl->add_variables(array(
			'target' => $url->get_acpmod_url(0,'&amp;action=cycle&amp;pos=0'),
			'incorrect_messages' => sprintf($locale->lang('incorrect_messages'),count($incorrect)),
			'incorrect_num' => count($incorrect)
		));
	}
}
?>