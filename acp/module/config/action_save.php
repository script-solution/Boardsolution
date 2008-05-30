<?php
/**
 * Contains the save-cfgitems-action
 *
 * @version			$Id: action_save.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The save-cfgitems-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_Config_save extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$helper = BS_ACP_Module_Config_Helper::get_instance();
		if($helper->get_manager()->save_changes())
		{
			$this->cache->refresh('config');
			
			$this->set_action_performed(true);
			$this->set_success_msg($this->locale->lang('settings_saved'));
		}

		return '';
	}
}
?>