<?php
/**
 * Contains the advanced-theme-editor-class
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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