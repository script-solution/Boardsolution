<?php
/**
 * Contains the default-submodule for tpleditor
 * 
 * @version			$Id: sub_default.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the tpleditor-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_tpleditor_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array();
	}
	
	public function run()
	{
		$helper = BS_ACP_Module_TplEditor_Helper::get_instance();
		$path = $helper->get_path();
		$def_path = $helper->get_path_in_default();

		$this->tpl->add_variables(array(
			'position' => $path,
			'parent_path' => $helper->get_parent_path(),
			'not_in_root' => $path != null && $path != ''
		));

		$dirs = array();
		$files = array();
		$dir = opendir($def_path);
		while($file = readdir($dir))
		{
			if($file != '.' && $file != '..' && $file != 'index.htm')
			{
				if(is_dir($def_path.'/'.$file))
					$dirs[] = $file;
				else if(($ext = PLIB_FileUtils::get_extension($file)) == 'htm' || $ext == 'css' || $ext == 'js')
					$files[] = $file;
			}
		}
		closedir($dir);

		sort($dirs);
		sort($files);
		$dir_content = $this->_array_merge_custom($dirs,$files);

		$items = array();
		$len = count($dir_content);
		for($i = 0;$i < $len;$i++)
		{
			$is_dir = $dir_content[$i]['type'] == 'dir';
			$item_name = $dir_content[$i]['name'];
			$exists_in_theme = true;
			
			if(is_file($path.'/'.$item_name))
				$file_path = $path.'/'.$item_name;
			else
			{
				$exists_in_theme = is_dir($path.'/'.$item_name);// || strpos($path,'themes') === false;
				$file_path = $def_path.'/'.$item_name;
			}

			if($is_dir)
			{
				$goto_path = ($path != '') ? $path.'/'.$item_name : $item_name;
				$url = $this->url->get_acpmod_url(0,'&amp;action=view&amp;path='.$goto_path);
				$edit_url = '';
				$image = '';
				$filesize = '';
			}
			else
			{
				$image = BS_ACP_Utils::get_instance()->get_file_image($file_path);
				$filesize = number_format(filesize($file_path),0,',','.');
				$edit_url = $this->url->get_acpmod_url(
					0,'&amp;action=edit&amp;path='.$path.'&amp;file='.$item_name
				);
			}

			if($time = filemtime($file_path))
				$last_modified = PLIB_Date::get_date($time);
			else
				$last_modified = '<i>Unknown</i>';

			$items[] = array(
				'url' => $url,
				'is_dir' => $is_dir,
				'name' => $item_name,
				'image' => $image,
				'filesize' => $filesize,
				'exists_in_theme' => $exists_in_theme,
				'last_modified' => $last_modified,
				'edit_url' => $edit_url
			);
		}
		
		$this->tpl->add_array('items',$items);
	}

	/**
	 * merges the given files and directories to one array
	 *
	 * @param array $dirs an numeric array with the directories
	 * @param array $files an numeric array with the files
	 * @return array an array of the form: array('type' => &lt;type&gt;,'name' => &lt;name&gt;)
	 */
	private function _array_merge_custom($dirs,$files)
	{
		$dir_content = array();
		$len = count($dirs);
		for($i = 0;$i < $len;$i++)
			$dir_content[] = array('type' => 'dir','name' => $dirs[$i]);

		$len = count($files);
		for($i = 0;$i < $len;$i++)
			$dir_content[] = array('type' => 'file','name' => $files[$i]);

		return $dir_content;
	}
	
	public function get_location()
	{
		return array();
	}
}
?>