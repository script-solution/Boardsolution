<?php
/**
 * Contains the editor-submodule for themes
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The editor sub-module for the themes-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_themes_editor extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();

		// hack which changes the action-type if we want to add an attribute instead of saving the form
		if($input->isset_var('add','post'))
			$input->set_var('action_type','post',BS_ACP_ACTION_THEME_EDITOR_SIMPLE_ADD);
		
		$renderer->add_action(BS_ACP_ACTION_THEME_EDITOR_SIMPLE_SAVE,'simplesave');
		$renderer->add_action(BS_ACP_ACTION_THEME_EDITOR_ADVANCED_SAVE,'advancedsave');
		$renderer->add_action(BS_ACP_ACTION_THEME_EDITOR_SIMPLE_DELETE,'simpledelete');
		$renderer->add_action(BS_ACP_ACTION_THEME_EDITOR_SIMPLE_ADD,'simpleadd');

		$mode = $input->correct_var(
			'mode','get',FWS_Input::STRING,array('simple','advanced'),'simple'
		);
		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		$renderer->add_breadcrumb($theme,'');
		
		$url = BS_URL::get_acpsub_url();
		$url->set('theme',$theme);
		$url->set('mode',$mode);
		$renderer->add_breadcrumb($locale->lang($mode.'_mode'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		$mode = $input->correct_var(
			'mode','get',FWS_Input::STRING,array('simple','advanced'),'simple'
		);
		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		
		$stylefile = FWS_Path::server_app().'themes/'.$theme.'/style.css';
		if(!is_file($stylefile))
		{
			$this->report_error(
				FWS_Document_Messages::ERROR,
				sprintf($locale->lang('file_not_exists'),$stylefile)
			);
			return;
		}
		
		if($mode == 'simple')
			$editor = new BS_ACP_Module_Themes_Editor_Simple();
		else
			$editor = new BS_ACP_Module_Themes_Editor_Advanced();
		
		$editor->display();
		
		$tpl->add_variables(array(
			'theme' => $theme,
			'editor_tpl' => $editor->get_template()
		));
	}
}
?>