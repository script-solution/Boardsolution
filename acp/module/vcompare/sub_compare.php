<?php
/**
 * Contains the compare-submodule for vcompare
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
 * The compare sub-module for the vcompare-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_vcompare_compare extends BS_ACP_SubModule
{
	/**
	 * Indicates that the files are equal
	 */
	const EQUAL						= 1;
	
	/**
	 * Indicates that both files exist but are not equal
	 */
	const CHANGE					= 2;
	
	/**
	 * Indicates that the file has been added
	 */
	const ADD							= 4;
	
	/**
	 * Indicates that the file has been removed
	 */
	const REMOVE					= 8;
	
	/**
	 * Indicates that the file has been changed by the user
	 */
	const USER_MODIFIED		= 16;
	
	/**
	 * define some subdirs that we don't want to compare
	 *
	 * @var array
	 */
	private static $_excl = array('dba/backups','images/smileys','images/avatars','install.php');
	
	/**
	 * define the root-folders and files we want to compare
	 *
	 * @var array
	 */
	private static $_allow = array(
		'acp','bbceditor','config','dba','extern','front','fws','images','install','language','src',
		'tools','themes','admin.php','index.php','standalone.php','.htaccess'
	);
	
	/**
	 * Regex to check excluded files/folders
	 *
	 * @var string
	 */
	private static $_exregex = null;
	
	/**
	 * Regex to check allowed files/folders
	 *
	 * @var string
	 */
	private static $_alregex = null;
	
	/**
	 * Stores the found paths to detect new files
	 * 
	 * @var array
	 */
	private $_found_paths = array();
	
	/**
	 * Stores all changed files that have to be replaced / added
	 *
	 * @var array
	 */
	private $_changed = array();
	
	/**
	 * Stores all conflicted files
	 *
	 * @var array
	 */
	private $_conflicts = array();
	
	/**
	 * All versions (instances of BS_Version)
	 *
	 * @var array
	 */
	private $_versions;
	
	/**
	 * The file with the version-infos
	 *
	 * @var string
	 */
	private $_file;
	
	/**
	 * The name of the version to compare to
	 *
	 * @var string
	 */
	private $_compare_version;
	
	/**
	 * Wether an error has occurred
	 *
	 * @var boolean
	 */
	private $_error = true;
	
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		// store the regex's for the arrays (just once)
		$excl = array();
		foreach(self::$_excl as $k => $e)
			$excl[$k] = preg_quote($e,'/');
		$allow = array();
		foreach(self::$_allow as $k => $e)
			$allow[$k] = preg_quote($e,'/');
		self::$_exregex = '/^('.implode('|',$excl).')/';
		self::$_alregex = '/^('.implode('|',$allow).')/';
		
		$input = FWS_Props::get()->input();
		$renderer = $doc->use_default_renderer();
		
		$http = new FWS_HTTP(BS_Version::VERSION_HOST);
		$versions = $http->get(BS_Version::VERSION_PATH);
		if($versions === false)
		{
			$this->report_error(
				FWS_Document_Messages::ERROR,$http->get_error_code().': '.$http->get_error_message()
			);
			return;
		}
		
		$this->_versions = BS_Version::read_versions($versions);
		
		$cbversions = array();
		foreach($this->_versions as $v)
			$cbversions[$v->get_id()] = $v->get_name();
		
		$compare = $input->get_var('compare','post',FWS_Input::STRING);
		if(!isset($cbversions[$compare]))
		{
			$this->report_error(
				FWS_Document_Messages::ERROR,'Version with id "'.$compare.'" doesn\'t exist!'
			);
			return;
		}
		
		// find the filename
		foreach($this->_versions as $v)
		{
			if($v->get_id() == $compare)
			{
				$this->_file = $v->get_id();
				$this->_compare_version = $v->get_name();
				break;
			}
		}
		
		$this->_error = false;
		$renderer->add_breadcrumb(BS_VERSION.' vs. '.$this->_compare_version);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();

		if($this->_error)
			return;
		
		$compare = $input->get_var('compare','post',FWS_Input::STRING);
		
		// now load the xml-document for that version
		$http = new FWS_HTTP('www.script-solution.de');
		$versioninfo = $http->get('/bsversions/v'.$this->_file.'.txt');
		if($versioninfo === false)
		{
			$this->report_error(
				FWS_Document_Messages::ERROR,$http->get_error_code().': '.$http->get_error_message()
			);
			return;
		}
		
		// load current version, if not already done
		if($this->_file != BS_VERSION_ID)
		{
			$cversioninfo = $http->get('/bsversions/v'.BS_VERSION_ID.'.txt');
			if($cversioninfo === false)
			{
				$this->report_error(
					FWS_Document_Messages::ERROR,$http->get_error_code().': '.$http->get_error_message()
				);
				return;
			}
		}
		else
			$cversioninfo = $versioninfo;
		
		// extract all paths with the md5-hashs from the xml-document
		$pathids = $this->_get_path_hashes($versioninfo);
		$cpathids = $this->_get_path_hashes($cversioninfo);
		
		// build a recursive structure of it
		$structure = $this->_build_structure('./',$pathids,$cpathids);
		
		// add files that are present in the compared version but not in the current one
		$paths = array();
		foreach($pathids as $id => $hash)
		{
			if($hash !== 1)
				$paths[$id] = $hash;
		}
		
		$missing = array_diff(array_keys($paths),array_keys($this->_found_paths));
		foreach($missing as $miss)
		{
			if(!preg_match(self::$_exregex,$miss) && preg_match(self::$_alregex,$miss))
			{
				$parts = explode('/',$miss);
				$name = '';
				$target = &$structure;
				for($i = 0,$len = count($parts) - 1;$i < $len;$i++)
				{
					$name .= $i > 0 ? '/'.$parts[$i] : $parts[$i];
					$vname = ($i < $len) ? '/'.$name : $name;
					if(isset($target[0][$vname]))
					{
						$target = &$target[0][$vname];
						if($target[1] & self::EQUAL)
						{
							$target[1] &= ~self::EQUAL;
							$target[1] |= self::CHANGE;
						}
					}
					else
					{
						$target[0][$vname] = array(array(),self::ADD);
						uksort($target[0],array($this,'_sort_paths_afterwards'));
						$target = &$target[0][$vname];
					}
				}
				$this->_changed[] = $miss;
				$target[0][$miss] = self::ADD;
				uksort($target[0],array($this,'_sort_paths_afterwards'));
			}
		}
		
		// now build the array for the templates
		$items = array();
		$this->_build_items($items,$structure,'');
		$tpl->add_variable_ref('items',$items);
		$tpl->add_variables(array(
			'current_version' => BS_VERSION,
			'compare_version' => $this->_compare_version,
			'download_changes_url' => 'http://www.script-solution.de/cgi-bin/genzip.php?id='.$compare,
			'add_color' => $this->_get_color(self::ADD),
			'change_color' => $this->_get_color(self::CHANGE),
			'remove_color' => $this->_get_color(self::REMOVE),
			'changed_paths' => $this->_changed,
			'conflict_paths' => $this->_conflicts
		));
	}
	
	/**
	 * Reads the path-hashes from the given response
	 *
	 * @param string $response the response
	 * @return array the path-hashes
	 */
	private function _get_path_hashes($response)
	{
		$paths = array();
		$vars = array();
		$lines = explode("\n",$response);
		for($i = 3,$linecount = count($lines);$i < $linecount;$i++)
		{
			$line = $lines[$i];
			if($line)
			{
				if($line[0] == '$')
				{
					list($key,$value) = explode('=',$line);
					$vars[$key] = $value;
				}
				else
				{
					$hash = FWS_String::substr($line,0,32);
					$path = FWS_String::substr($line,33);
					$slash = strpos($path,'/');
					$var = FWS_String::substr($path,0,$slash);
					$path = $vars[$var].FWS_String::substr($path,$slash);
					if(FWS_String::starts_with($path,'./'))
						$path = FWS_String::substr($path,2);
					$paths[$path] = $hash;
					// add parent-dirs
					while(($path = dirname($path)) != '.')
						$paths[$path] = 1;
				}
			}
		}
		
		return $paths;
	}
	
	/**
	 * Determines the color for the given difference
	 *
	 * @param int $diff
	 * @return string the color
	 */
	private function _get_color($diff)
	{
		switch($diff & ~self::USER_MODIFIED)
		{
			case self::ADD:
				return '#86f483';
			case self::CHANGE:
				return '#8de5ef';
			case self::REMOVE:
				return '#ff9f9f';
			default:
				return '';
		}
	}
	
	/**
	 * Builds the items for the template
	 *
	 * @param array $items reference to the items
	 * @param array $folder the current folder: <code>array(<items>,<changed>)</code>
	 * @param string $name the name of the folder
	 * @param int $layer the current layer
	 */
	private function _build_items(&$items,$folder,$name,$layer = 0)
	{
		$locale = FWS_Props::get()->locale();

		// we don't want to have a root-folder for all
		if($layer > 0)
		{
			$items[] = array(
				'layer' => $layer - 1,
				'name' => basename($name),
				'isfile' => false,
				'image' => '',
				'layerend' => false,
				'id' => str_replace('/','_',$name),
				'usermodified' => $folder[1] & self::USER_MODIFIED,
				'color' => $this->_get_color($folder[1])
			);
		}
		
		// loop through all items
		foreach($folder[0] as $path => $item)
		{
			if(is_array($item))
				$this->_build_items($items,$item,FWS_String::substr($path,1),$layer + 1);
			else
			{
				if(($item & self::ADD) == 0)
				{
					$size = number_format(
						filesize($path),
						0,
						$locale->get_thousands_separator(),
						$locale->get_dec_separator()
					);
					$changed = FWS_Date::get_date(filemtime($path));
				}
				
				$items[] = array(
					'layer' => $layer,
					'name' => basename($path),
					'isfile' => true,
					'image' => BS_ACP_Utils::get_file_image($path),
					'id' => str_replace('/','_',$path),
					'layerend' => false,
					'color' => $this->_get_color($item),
					'usermodified' => $item & self::USER_MODIFIED,
					'size' => ($item & self::ADD) ? $locale->lang('notavailable') : $size.' Bytes',
					'changed' => ($item & self::ADD) ? $locale->lang('notavailable') : $changed
				);
			}
		}
		
		if($layer > 0)
		{
			$items[] = array(
				'layerend' => true
			);
		}
	}
	
	/**
	 * Builds a recursive structure for the local filesystem
	 *
	 * @param string $dir the current folder
	 * @param array $paths the paths of the compare version
	 * @param array $cpaths the paths of the current version
	 * @return array the recursive structure
	 */
	private function _build_structure($dir,$paths,$cpaths)
	{
		// read the folder-content and sort it
		$structure = array();
		$changed = self::EQUAL;
		$items = FWS_FileUtils::get_list($dir,false,true);
		usort($items,array($this,'_sort_paths'));
		
		foreach($items as $item)
		{
			// cut the "./"
			$item = FWS_String::substr($item,2);
			// do we want to compare the file?
			if(strpos($item,'.svn') === false && !preg_match(self::$_exregex,$item) &&
					preg_match(self::$_alregex,$item))
			{
				if(is_dir($item))
				{
					$res = $this->_build_structure('./'.$item,$paths,$cpaths);
					$structure['/'.$item] = $res;
					if(($res[1] & self::EQUAL) == 0)
						$changed = self::CHANGE | ($changed & self::USER_MODIFIED);
					if($res[1] & self::USER_MODIFIED)
						$changed |= self::USER_MODIFIED;
				}
				else
				{
					// if the compare-version hasn't this file it has been removed
					if(!isset($paths[$item]))
					{
						$changed = self::CHANGE | ($changed & self::USER_MODIFIED);
						$structure[$item] = self::REMOVE;
					}
					// otherwise we check if the hashs are equal
					else
					{
						$this->_found_paths[$item] = 1;
						$hash = md5_file($item);
						if($hash != $paths[$item])
						{
							$changed = self::CHANGE | ($changed & self::USER_MODIFIED);
							$this->_changed[] = $item;
						}
						$structure[$item] = $hash != $paths[$item] ? self::CHANGE : self::EQUAL;
					}
					
					// compare with current version
					if(!isset($cpaths[$item]))
					{
						$structure[$item] |= self::USER_MODIFIED;
						$changed |= self::USER_MODIFIED;
					}
					else
					{
						if($structure[$item] == self::REMOVE)
							$hash = md5_file($item);
						if($hash != $cpaths[$item] && $hash != $paths[$item])
						{
							$structure[$item] |= self::USER_MODIFIED;
							$changed |= self::USER_MODIFIED;
							$this->_conflicts[] = $item;
						}
					}
				}
			}
		}
		
		if(isset($paths[substr($dir,2)]))
			$status = $changed;
		else
			$status = self::REMOVE | ($changed & self::USER_MODIFIED);
		return array($structure,$status);
	}
	
	/**
	 * The sort-callback to sort one directory by name (folders first)
	 *
	 * @param string $a the first path
	 * @param string $b the second path
	 * @return int the compare-result
	 */
	private function _sort_paths($a,$b)
	{
		$afile = is_file($a);
		$bfile = is_file($b);
		if($afile && !$bfile)
			return 1;
		if($bfile && !$afile)
			return -1;
		
		return strcasecmp(basename($a),basename($b));
	}
	
	/**
	 * The sort-callback to sort one directory by name (folders first)
	 *
	 * @param string $a the first path
	 * @param string $b the second path
	 * @return int the compare-result
	 */
	private function _sort_paths_afterwards($a,$b)
	{
		$afile = $a[0] != '/';
		$bfile = $b[0] != '/';
		if($afile && !$bfile)
			return 1;
		if($bfile && !$afile)
			return -1;
		
		return strcasecmp(basename($a),basename($b));
	}
}
?>