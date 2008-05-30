<?php
/**
 * Contains the edit-submodule for smileys
 * 
 * @version			$Id: sub_edit.php 795 2008-05-29 18:22:45Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The submodule for editing smileys
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_smileys_edit extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_EDIT_SMILEY => 'edit'
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
		
		$data = BS_DAO::get_smileys()->get_by_id($id);
		if($data === false)
		{
			$this->_report_error();
			return;
		}

		$this->_request_formular();
		
		$this->tpl->add_array('smiley',$data);
		$this->tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_EDIT_SMILEY,
			'site' => $this->input->get_var('site','get',PLIB_Input::INTEGER),
			'id' => $id
		));
	}
	
	public function get_location()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		return array(
			$this->locale->lang('edit_smiley') => $this->url->get_acpmod_url(
				0,'&amp;action=edit&amp;id='.$id
			)
		);
	}
}
?>