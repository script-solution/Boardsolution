<?php
/**
 * Contains the user-ranks module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The user-ranks-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_userranks extends BS_ACP_Module
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_UPDATE_USERRANKS => 'update',
			BS_ACP_ACTION_ADD_USERRANK => 'add',
			BS_ACP_ACTION_DELETE_USERRANKS => 'delete'
		);
	}
	
	public function run()
	{
		if(($ids = $this->input->get_var('delete','post')) != null)
		{
			$names = $this->cache->get_cache('user_ranks')->get_field_vals_of_keys($ids,'rank');
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_USERRANKS.'&amp;ids='.implode(',',$ids)
				),
				$this->url->get_acpmod_url()
			);
		}
		
		$this->tpl->add_variables(array(
			'at_update' => BS_ACP_ACTION_UPDATE_USERRANKS,
			'at_add' => BS_ACP_ACTION_ADD_USERRANK
		));
	
		$ranks = $this->cache->get_cache('user_ranks')->get_elements();
		$this->tpl->add_array('ranks',$ranks);
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('acpmod_userranks') => $this->url->get_acpmod_url()
		);
	}
}
?>