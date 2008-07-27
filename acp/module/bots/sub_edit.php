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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$input = PLIB_Props::get()->input();
		
		$doc->add_action(BS_ACP_ACTION_EDIT_BOT,'edit');
		$doc->set_template('bots_edit.htm');
		
		$id = $input->get_var('id','get',PLIB_Input::ID);
		$doc->add_breadcrumb(
			$locale->lang('edit_bot'),
			$url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		$id = $input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}
		
		$data = $cache->get_cache('bots')->get_element($id);
		if($data === null)
		{
			$this->report_error();
			return;
		}
		
		$this->request_formular();
		
		$site = $input->get_var('site','get',PLIB_Input::INTEGER);
		$tpl->add_variables(array(
			'default' => $data,
			'site' => $site,
			'action_type' => BS_ACP_ACTION_EDIT_BOT,
			'title' => $locale->lang('edit_bot'),
			'form_target' => $url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id.'&amp;site='.$site)
		));
	}
}
?>