<?php
/**
 * Contains the add-themes-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		$theme_name = $this->input->get_var('theme_name','post',PLIB_Input::STRING);
		$theme_folder = $this->input->get_var('theme_folder','post',PLIB_Input::STRING);
		if(!preg_match('/^[a-z0-9_]+$/i',$theme_folder))
			return 'invalid_theme_folder_name';
		
		if($this->cache->get_cache('themes')->element_exists_with(array('theme_folder' => $theme_folder)))
			return 'theme_exists';

		BS_DAO::get_themes()->create($theme_name,$theme_folder);
		$this->cache->refresh('themes');

		$msg = '';
		
		$result = $this->_create_directory(PLIB_Path::inner().'themes/'.$theme_folder);
		if(!$result)
			$msg = $this->locale->lang('error_creating_directory_structure_failed').'<br />';

		if($msg == '')
		{
			$result = $this->_create_directory(PLIB_Path::inner().'themes/'.$theme_folder.'/templates');
			if(!$result)
				$msg = $this->locale->lang('error_creating_directory_structure_failed').'<br />';
		}
		
		if($msg == '')
		{
			$result = $this->_create_directory(PLIB_Path::inner().'themes/'.$theme_folder.'/images');
			if(!$result)
				$msg = $this->locale->lang('error_creating_directory_structure_failed').'<br />';
		}

		if($msg == '')
		{
			$result = PLIB_FileUtils::copy(
				PLIB_Path::inner().'themes/default/style.css',
				PLIB_Path::inner().'themes/'.$theme_folder.'/style.css'
			);
			if(!$result)
				$msg = $this->locale->lang('error_creating_directory_structure_failed').'<br />';
		}

		// don't report errors here. the index-files are not really important
		$this->_create_index_file(PLIB_Path::inner().'themes/'.$theme_folder);
		$this->_create_index_file(PLIB_Path::inner().'themes/'.$theme_folder.'/templates');
		$this->_create_index_file(PLIB_Path::inner().'themes/'.$theme_folder.'/images');
		
		$this->set_success_msg($msg.$this->locale->lang('theme_add_success'));
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
			if(@chown($path,fileowner(PLIB_Path::inner().'themes/default')))
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
		return PLIB_FileUtils::write($directory.'/index.htm',$content) !== false;
	}
}
?>