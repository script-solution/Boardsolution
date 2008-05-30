<?php
/**
 * Contains the edit-submodule for bots
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The submodule for editing bots
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_bots_edit extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_EDIT_BOT => 'edit'
		);
	}
	
	public function run()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$this->_report_error();
			return;
		}
		
		$data = $this->cache->get_cache('bots')->get_element($id);
		if($data === null)
		{
			$this->_report_error();
			return;
		}
		
		$this->_request_formular();
		
		$site = $this->input->get_var('site','get',PLIB_Input::INTEGER);
		$this->tpl->add_variables(array(
			'default' => $data,
			'site' => $site,
			'action_type' => BS_ACP_ACTION_EDIT_BOT,
			'title' => $this->locale->lang('edit_bot'),
			'form_target' => $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id.'&amp;site='.$site)
		));
	}
	
	public function get_location()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		return array(
			$this->locale->lang('edit_bot') => $this->url->get_acpmod_url(
				0,'&amp;action=edit&amp;id='.$id
			)
		);
	}
}
?>