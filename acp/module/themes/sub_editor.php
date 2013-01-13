<?php
/**
 * Contains the editor-submodule for themes
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
		$path = FWS_Path::server_app().'themes/'.$theme.'/';
		
		$cssfiles = array();
		if($mode == 'simple')
		{
			$stylefile = $path.'basic.css';
			if(!is_file($stylefile))
			{
				$this->report_error(
					FWS_Document_Messages::ERROR,
					sprintf($locale->lang('file_not_exists'),$stylefile)
				);
				return;
			}
		
			$editor = new BS_ACP_Module_Themes_Editor_Simple();
		}
		else
		{
			$editor = new BS_ACP_Module_Themes_Editor_Advanced();
			foreach(FWS_FileUtils::get_list($path,false,false) as $item)
			{
				if(FWS_String::ends_with($item,'.css'))
					$cssfiles[] = $item;
			}
		}
		
		$editor->display();
		
		$tpl->add_variables(array(
			'theme' => $theme,
			'mode' => $mode,
			'cssfiles' => $cssfiles,
			'editor_tpl' => $editor->get_template()
		));
	}
}
?>