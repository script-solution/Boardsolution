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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$input = FWS_Props::get()->input();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_EDIT_BOT,'edit');
		$renderer->set_template('bots_edit.htm');
		
		$id = $input->get_var('id','get',FWS_Input::ID);
		$renderer->add_breadcrumb(
			$locale->lang('edit_bot'),
			$url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();

		$id = $input->get_var('id','get',FWS_Input::ID);
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
		
		$site = $input->get_var('site','get',FWS_Input::INTEGER);
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