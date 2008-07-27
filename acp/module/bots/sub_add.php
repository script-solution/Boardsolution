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
		
		$doc->add_action(BS_ACP_ACTION_ADD_BOT,'add');
		$doc->set_template('bots_edit.htm');
		$doc->add_breadcrumb($locale->lang('add_bot'),$url->get_acpmod_url(0,'&amp;action=add'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		$data = array(
			'bot_name' => '',
			'bot_match' => '',
			'bot_ip_start' => '',
			'bot_ip_end' => '',
			'bot_access' => 1
		);
		
		$this->request_formular();
		
		$site = $input->get_var('site','get',PLIB_Input::INTEGER);
		$tpl->add_variables(array(
			'default' => $data,
			'site' => $site,
			'action_type' => BS_ACP_ACTION_ADD_BOT,
			'title' => $locale->lang('add_bot'),
			'form_target' => $url->get_acpmod_url(0,'&amp;action=add&amp;site='.$site)
		));
	}
}
?>