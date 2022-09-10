<?php
/**
 * Contains the add-themes-action
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
 * The add-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_add extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$theme_name = $input->get_var('theme_name','post',FWS_Input::STRING);
		$theme_folder = $input->get_var('theme_folder','post',FWS_Input::STRING);
		if($theme_folder == '' || !preg_match('/^[a-z0-9_]+$/i',$theme_folder))
			return 'invalid_theme_folder_name';
		
		if($cache->get_cache('themes')->element_exists_with(array('theme_folder' => $theme_folder)))
			return 'theme_exists';

		BS_DAO::get_themes()->create($theme_name,$theme_folder);
		$cache->refresh('themes');

		$msg = '';
		
		$result = $this->_create_directory(FWS_Path::server_app().'themes/'.$theme_folder);
		if(!$result)
			$msg = $locale->lang('error_creating_directory_structure_failed').'<br />';

		if($msg == '')
		{
			$result = $this->_create_directory(FWS_Path::server_app().'themes/'.$theme_folder.'/templates');
			if(!$result)
				$msg = $locale->lang('error_creating_directory_structure_failed').'<br />';
		}
		
		if($msg == '')
		{
			$result = $this->_create_directory(FWS_Path::server_app().'themes/'.$theme_folder.'/images');
			if(!$result)
				$msg = $locale->lang('error_creating_directory_structure_failed').'<br />';
		}

		if($msg == '')
		{
			$result = FWS_FileUtils::copy(
				FWS_Path::server_app().'themes/default/style.css',
				FWS_Path::server_app().'themes/'.$theme_folder.'/style.css'
			);
			if(!$result)
				$msg = $locale->lang('error_creating_directory_structure_failed').'<br />';
		}

		// don't report errors here. the index-files are not really important
		$this->_create_index_file(FWS_Path::server_app().'themes/'.$theme_folder);
		$this->_create_index_file(FWS_Path::server_app().'themes/'.$theme_folder.'/templates');
		$this->_create_index_file(FWS_Path::server_app().'themes/'.$theme_folder.'/images');
		
		$this->set_success_msg($msg.$locale->lang('theme_add_success'));
		$this->set_action_performed(true);

		return '';
	}

	/**
	 * creates a new directory
	 *
	 * @param string $path the directory-path
	 * @return boolean true if successfull
	 */
	private function _create_directory($path)
	{
		if(@mkdir($path))
		{
			if(@chown($path,fileowner(FWS_Path::server_app().'themes/default')))
				return @chmod($path,0777);
		}

		return false;
	}

	/**
	 * creates an index.htm in the given directory
	 *
	 * @param string $directory the directory
	 * @return boolean true if successfull
	 */
	private function _create_index_file($directory)
	{
		$content = "<html>\r\n<body>\r\n</body>\r\n</html>";
		return FWS_FileUtils::write($directory.'/index.htm',$content) !== false;
	}
}
?>