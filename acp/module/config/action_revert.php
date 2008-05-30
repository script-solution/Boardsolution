<?php
/**
 * Contains the revert-cfgitems-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The revert-cfgitems-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_Config_revert extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';
		
		$helper = BS_ACP_Module_Config_Helper::get_instance();
		if($helper->get_manager()->revert_item($id))
		{
			$this->cache->refresh('config');
			
			$this->set_action_performed(true);
			$this->set_success_msg($this->locale->lang('setting_reverted'));
		}
		
		$this->set_action_performed(true);

		return '';
	}
}
?>