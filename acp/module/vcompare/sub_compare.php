<?php
/**
 * Contains the compare-submodule for vcompare
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	const EQUAL = 0;
	
	/**
	 * Indicates that both files exist but are not equal
	 */
	const CHANGE = 1;
	
	/**
	 * Indicates that the file has been added
	 */
	const ADD = 2;
	
	/**
	 * Indicates that the file has been removed
	 */
	const REMOVE = 3;
	
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
	 * All versions
	 *
	 * @var string
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
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$http = new PLIB_HTTP('www.script-solution.de');
		$this->_versions = $http->get('/bsversions/versions.xml');
		if($this->_versions === false)
		{
			$this->_report_error(
				PLIB_Messages::MSG_TYPE_ERROR,$http->get_error_code().': '.$http->get_error_message()
			);
			return;
		}
		
		$xml = new SimpleXMLElement($this->_versions);
		$cbversions = array();
		foreach($xml->version as $v)
			$cbversions[(string)$v['id']] = (string)$v['name'];
		
		$compare = $this->input->get_var('compare','post',PLIB_Input::STRING);
		if(!isset($cbversions[$compare]))
		{
			$this->_report_error(
				PLIB_Messages::MSG_TYPE_ERROR,'Version with id "'.$compare.'" doesn\'t exist!'
			);
			return;
		}
		
		// find the filename
		foreach($xml->version as $v)
		{
			if((string)$v['id'] == $compare)
			{
				$this->_file = (string)$v['id'];
				$this->_compare_version = (string)$v;
				break;
			}
		}
		
		$this->_error = false;
	}
	
	public function run()
	{
		if($this->_error)
			return;
		
		$compare = $this->input->get_var('compare','post',PLIB_Input::STRING);
		
		// now load the xml-document for that version
		$http = new PLIB_HTTP('www.script-solution.de');
		$versioninfo = $http->get('/bsversions/v'.$this->_file.'.txt');
		if($versioninfo === false)
		{
			$this->_report_error(
				PLIB_Messages::MSG_TYPE_ERROR,$http->get_error_code().': '.$http->get_error_message()
			);
			return;
		}
		
		// extract all paths with the md5-hashs from the xml-document
		$pathids = $this->_get_path_hashes($versioninfo);
		
		// build a recursive structure of it
		$structure = $this->_build_structure('./',$pathids);
		
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
			$parts = explode('/',$miss);
			$name = '';
			$target = &$structure;
			for($i = 0,$len = count($parts) - 1;$i < $len;$i++)
			{
				$name .= $i > 0 ? '/'.$parts[$i] : $parts[$i];
				$vname = ($i < $len) ? '/'.$name : $name;
				if(isset($target[0][$vname]))
					$target = &$target[0][$vname];
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
		
		// now build the array for the templates
		$items = array();
		$this->_build_items($items,$structure,'');
		$this->tpl->add_array('items',$items);
		$this->tpl->add_variables(array(
			'current_version' => BS_VERSION,
			'compare_version' => $this->_compare_version,
			'download_changes_url' => 'http://www.script-solution.de/cgi-bin/genzip.php?id='.$compare,
			'add_color' => $this->_get_color(self::ADD),
			'change_color' => $this->_get_color(self::CHANGE),
			'remove_color' => $this->_get_color(self::REMOVE),
			'changed_paths' => $this->_changed
		));
	}
	
	public function get_location()
	{
		return array(
			BS_VERSION.' vs. '.$this->_compare_version => ''
		);
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
					$hash = PLIB_String::substr($line,0,32);
					$path = PLIB_String::substr($line,33);
					$slash = strpos($path,'/');
					$var = PLIB_String::substr($path,0,$slash);
					$path = $vars[$var].PLIB_String::substr($path,$slash);
					if(PLIB_String::starts_with($path,'./'))
						$path = PLIB_String::substr($path,2);
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
		switch($diff)
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
				'color' => $this->_get_color($folder[1])
			);
		}
		
		// loop through all items
		foreach($folder[0] as $path => $item)
		{
			if(is_array($item))
				$this->_build_items($items,$item,PLIB_String::substr($path,1),$layer + 1);
			else
			{
				if($item != self::ADD)
				{
					$size = number_format(
						filesize($path),
						0,
						$this->locale->get_thousands_separator(),
						$this->locale->get_dec_separator()
					);
					$changed = PLIB_Date::get_date(filemtime($path));
				}
				
				$items[] = array(
					'layer' => $layer,
					'name' => basename($path),
					'isfile' => true,
					'image' => BS_ACP_Utils::get_instance()->get_file_image($path),
					'id' => str_replace('/','_',$path),
					'layerend' => false,
					'color' => $this->_get_color($item),
					'size' => $item == self::ADD ? $this->locale->lang('notavailable') : $size.' Bytes',
					'changed' => $item == self::ADD ? $this->locale->lang('notavailable') : $changed
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
	 * @param array $paths the paths of the compare-version
	 * @return array the recursive structure
	 */
	private function _build_structure($dir,$paths)
	{
		// define some subdirs that we don't want to compare
		static $excl = array('dba/backups','images/smileys','images/avatars');
		// define the root-folders and files we want to compare
		static $allow = array(
			'acp','bbceditor','config','dba','extern','front','images','install','language','src','themes',
			'admin.php','index.php','standalone.php','.htaccess'
		);
		// store the regex's for the arrays (just once)
		static $exregex = null;
		static $alregex = null;
		if($exregex === null)
		{
			foreach($excl as $k => $e)
				$excl[$k] = preg_quote($e,'/');
			foreach($allow as $k => $e)
				$allow[$k] = preg_quote($e,'/');
			$exregex = '/^('.implode('|',$excl).')/';
			$alregex = '/^('.implode('|',$allow).')/';
		}
		
		// read the folder-content and sort it
		$structure = array();
		$changed = self::EQUAL;
		$items = PLIB_FileUtils::get_dir_content($dir,false,true);
		usort($items,array($this,'_sort_paths'));
		
		foreach($items as $item)
		{
			// cut the "./"
			$item = PLIB_String::substr($item,2);
			// do we want to compare the file?
			if(strpos($item,'.svn') === false && !preg_match($exregex,$item) && preg_match($alregex,$item))
			{
				if(is_dir($item))
				{
					$res = $this->_build_structure('./'.$item,$paths);
					$structure['/'.$item] = $res;
					if($res[1] != self::EQUAL)
						$changed = self::CHANGE;
				}
				else
				{
					// if the compare-version hasn't this file it has been removed
					if(!isset($paths[$item]))
					{
						$changed = self::CHANGE;
						$structure[$item] = self::REMOVE;
						$this->_changed[] = $item;
					}
					// otherwise we check if the hashs are equal
					else
					{
						$this->_found_paths[$item] = 1;
						$hash = md5_file($item);
						if($hash != $paths[$item])
						{
							$changed = self::CHANGE;
							$this->_changed[] = $item;
						}
						$structure[$item] = $hash != $paths[$item] ? self::CHANGE : self::EQUAL;
					}
				}
			}
		}
		
		return array($structure,isset($paths[substr($dir,2)]) ? $changed : self::REMOVE);
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