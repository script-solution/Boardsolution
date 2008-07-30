<?php
/**
 * Contains the edit-submodule for the linklist
 * 
 * @version			$Id$
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_LINK,'edit');
		
		$id = $input->get_var('id','get',PLIB_Input::ID);
		$renderer->add_breadcrumb(
			$locale->lang('edit'),
			$url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();

		$id = $input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}
		
		$data = BS_DAO::get_links()->get_by_id($id);
		if($data === false)
		{
			$this->report_error();
			return;
		}
		
		$options = array();
		foreach(BS_DAO::get_links()->get_categories() as $name)
			$options[$name] = $name;

		$this->request_formular();
		
		// load posting-form
		$form = new BS_PostingForm(
			$locale->lang('email_text').':',$data['link_desc_posted'],'desc'
		);
		
		// set colspan for the post-form-template
		$tpl->set_template('inc_post_form.htm');
		$tpl->add_variables(array(
			'colspan_main' => 1
		));
		$tpl->restore_template();
		
		$form->add_form();

		$tpl->add_array('data',$data);
		$tpl->add_variables(array(
			'id' => $id,
			'categories' => $options
		));
	}
}
?>