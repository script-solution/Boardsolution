<?php
/**
 * Contains the banlist module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The banlist-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_banlist extends BS_ACP_Module
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_BANS => 'delete',
			BS_ACP_ACTION_ADD_BAN => 'add',
			BS_ACP_ACTION_UPDATE_BANS => 'update'
		);
	}
	
	public function run()
	{
		if(($delete = $this->input->get_var('delete','post')) != null)
		{
			$names = $this->cache->get_cache('banlist')->get_field_vals_of_keys($delete,'bann_name');
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(
					0,'&amp;ids='.implode(',',$delete).'&amp;at='.BS_ACP_ACTION_DELETE_BANS
				),
				$this->url->get_acpmod_url()
			);
		}
		
		$this->_request_formular();
		
		$type_array = array(
			'ip' => $this->locale->lang('ip_address'),
			'user' => $this->locale->lang('username'),
			'mail' => $this->locale->lang('email_adress')
		);

		$this->tpl->add_variables(array(
			'types' => $type_array,
			'entries' => $this->cache->get_cache('banlist')->get_elements(),
			'action_type_update' => BS_ACP_ACTION_UPDATE_BANS,
			'action_type_add' => BS_ACP_ACTION_ADD_BAN
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('acpmod_bans') => $this->url->get_acpmod_url()
		);
	}
}
?>