<?php
/**
 * Contains the add-submodule for themes
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add sub-module for the themes-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_themes_add extends BS_ACP_SubModule
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
		
		$doc->add_action(BS_ACP_ACTION_ADD_THEME,'add');

		$doc->add_breadcrumb(
			$locale->lang('add_theme'),
			$url->get_acpmod_url(0,'&amp;action=add')
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$tpl = PLIB_Props::get()->tpl();

		$this->request_formular();
		$tpl->add_variables(array(
			'at_add' => BS_ACP_ACTION_ADD_THEME
		));
	}
}
?>