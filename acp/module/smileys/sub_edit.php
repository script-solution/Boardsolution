<?php
/**
 * Contains the edit-submodule for smileys
 * 
 * @version			$Id$
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
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_SMILEY,'edit');

		$id = $input->get_var('id','get',PLIB_Input::ID);
		$renderer->add_breadcrumb(
			$locale->lang('edit_smiley'),
			$url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();

		$id = $input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}
		
		$data = BS_DAO::get_smileys()->get_by_id($id);
		if($data === false)
		{
			$this->report_error();
			return;
		}

		$this->request_formular();
		
		$tpl->add_array('smiley',$data);
		$tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_EDIT_SMILEY,
			'site' => $input->get_var('site','get',PLIB_Input::INTEGER),
			'id' => $id
		));
	}
}
?>