<?php
/**
 * Contains the add-submodule for bots
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The submodule for adding bots
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_bots_add extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_ADD_BOT => 'add'
		);
	}
	
	public function get_template()
	{
		return 'bots_edit.htm';
	}
	
	public function run()
	{
		$data = array(
			'bot_name' => '',
			'bot_match' => '',
			'bot_ip_start' => '',
			'bot_ip_end' => '',
			'bot_access' => 1
		);
		
		$this->_request_formular();
		
		$site = $this->input->get_var('site','get',PLIB_Input::INTEGER);
		$this->tpl->add_variables(array(
			'default' => $data,
			'site' => $site,
			'action_type' => BS_ACP_ACTION_ADD_BOT,
			'title' => $this->locale->lang('add_bot'),
			'form_target' => $this->url->get_acpmod_url(0,'&amp;action=add&amp;site='.$site)
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('add_bot') => $this->url->get_acpmod_url(0,'&amp;action=add')
		);
	}
}
?>