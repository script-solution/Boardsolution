<?php
/**
 * Contains the edit-submodule for the linklist
 * 
 * @version			$Id: sub_edit.php 725 2008-05-22 15:48:16Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit submodule for the linklist
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_linklist_edit extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_EDIT_LINK => 'edit'
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
		
		$data = BS_DAO::get_links()->get_by_id($id);
		if($data === false)
		{
			$this->_report_error();
			return;
		}
		
		$options = array();
		foreach(BS_DAO::get_links()->get_categories() as $name)
			$options[$name] = $name;

		$this->_request_formular();
		
		// load posting-form
		$form = new BS_PostingForm(
			$this->locale->lang('email_text').':',$data['link_desc_posted'],'lnkdesc'
		);
		
		// set colspan for the post-form-template
		$this->tpl->set_template('inc_post_form.htm');
		$this->tpl->add_variables(array(
			'colspan_main' => 1
		));
		$this->tpl->restore_template();
		
		$form->add_form();

		$this->tpl->add_array('data',$data);
		$this->tpl->add_variables(array(
			'id' => $id,
			'categories' => $options
		));
	}
	
	public function get_location()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		return array(
			$this->locale->lang('edit') => $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
}
?>