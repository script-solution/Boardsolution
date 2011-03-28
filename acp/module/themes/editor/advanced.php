<?php
/**
 * Contains the advanced-theme-editor-class
 * 
 * @version			$Id$
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
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$file = $input->get_var('file','get',FWS_Input::STRING);
		if(!preg_match('/^[a-zA-Z0-9_]+\.css$/',$file))
			$file = 'basic.css';
		$path = $this->_theme.'/'.$file;
		$theme = $input->get_var('theme','get',FWS_Input::STRING);

		$url = BS_URL::get_acpsub_url(0,'editor');
		$url->set('theme',$theme);
		$url->set('mode','advanced');
		$url->set('file',$file);
		
		$tpl->set_template('tpleditor_formular.htm');
		$tpl->add_variables(array(
			'target_url' => $url->to_url(),
			'action_type' => BS_ACP_ACTION_THEME_EDITOR_ADVANCED_SAVE,
			'image' => BS_ACP_Utils::get_file_image($file),
			'filename' => $path,
			'filesize' => number_format(filesize($path),0,',','.'),
			'last_modification' => FWS_Date::get_date(filemtime($path)),
			'file_content' => trim(file_get_contents($path)),
			'back_button' => false
		));
		$tpl->restore_template();
	}
	
	public function get_template()
	{
		return 'tpleditor_formular.htm';
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>