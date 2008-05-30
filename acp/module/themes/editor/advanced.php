<?php
/**
 * Contains the advanced-theme-editor-class
 * 
 * @version			$Id: advanced.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The advanced theme-editor
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Themes_Editor_Advanced extends BS_ACP_Module_Themes_Editor_Base
{
	/**
	 * Displays the advanced-mode-theme-editor
	 */
	public function display()
	{
		$file = $this->_theme.'/style.css';
		$theme = $this->input->get_var('theme','get',PLIB_Input::STRING);

		$this->tpl->set_template('tpleditor_formular.htm');
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_acpmod_url(
				0,'&amp;action=editor&amp;theme='.$theme.'&amp;mode=advanced'
			),
			'action_type' => BS_ACP_ACTION_THEME_EDITOR_ADVANCED_SAVE,
			'image' => BS_ACP_Utils::get_instance()->get_file_image($file),
			'filename' => $file,
			'filesize' => number_format(filesize($file),0,',','.'),
			'last_modification' => PLIB_Date::get_date(filemtime($file)),
			'file_content' => trim(file_get_contents($file)),
			'back_button' => false
		));
		$this->tpl->restore_template();
	}
	
	public function get_template()
	{
		return 'tpleditor_formular.htm';
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>