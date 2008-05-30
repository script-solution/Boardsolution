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
	 * Constructor
	 */
	public function __construct()
	{
		// hack which changes the action-type if we want to add an attribute instead of saving the form
		if($this->input->isset_var('add','post'))
			$this->input->set_var('action_type','post',BS_ACP_ACTION_THEME_EDITOR_SIMPLE_ADD);
	}
	
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_THEME_EDITOR_SIMPLE_SAVE => 'simplesave',
			BS_ACP_ACTION_THEME_EDITOR_ADVANCED_SAVE => 'advancedsave',
			BS_ACP_ACTION_THEME_EDITOR_SIMPLE_DELETE => 'simpledelete',
			BS_ACP_ACTION_THEME_EDITOR_SIMPLE_ADD => 'simpleadd',
		);
	}
	
	public function run()
	{
		$mode = $this->input->correct_var(
			'mode','get',PLIB_Input::STRING,array('simple','advanced'),'simple'
		);
		$theme = $this->input->get_var('theme','get',PLIB_Input::STRING);
		
		$stylefile = PLIB_Path::inner().'themes/'.$theme.'/style.css';
		if(!is_file($stylefile))
		{
			$this->_report_error(
				PLIB_Messages::MSG_TYPE_ERROR,
				sprintf($this->locale->lang('file_not_exists'),$stylefile)
			);
			return;
		}
		
		if($mode == 'simple')
			$editor = new BS_ACP_Module_Themes_Editor_Simple();
		else
			$editor = new BS_ACP_Module_Themes_Editor_Advanced();
		
		$editor->display();
		
		$this->tpl->add_variables(array(
			'theme' => $theme,
			'editor_tpl' => $editor->get_template()
		));
	}
	
	public function get_location()
	{
		$mode = $this->input->correct_var(
			'mode','get',PLIB_Input::STRING,array('simple','advanced'),'simple'
		);
		$theme = $this->input->get_var('theme','get',PLIB_Input::STRING);
		return array(
			$theme => '',
			$this->locale->lang($mode.'_mode') =>
				$this->url->get_acpmod_url(0,'&amp;action=editor&amp;theme='.$theme.'&amp;mode='.$mode)
		);
	}
}
?>