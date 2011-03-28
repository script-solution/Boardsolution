<?php
/**
 * Contains the autoloader
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The autoloader for the Boardsolution src-files
 * 
 * @param string $item the item to load
 * @return boolean wether the file has been loaded
 */
function BS_Autoloader($item)
{
	// Note that we don't use the MB-functions here for performance issues
	if(substr($item,0,3) == 'BS_')
	{
		static $folders = null;
		if($folders === null)
			$folders = array('front' => 1,'acp' => 1,'install' => 1,'dba' => 1,'extern' => 1);
		
		$nitem = substr($item,3);
		$folder = strtolower(strtok($nitem,'_'));
		$subfolder = 'src/';
		if(!isset($folders[$folder]))
			$folder = '';
		else
		{
			// allow includes in the module-folders
			$parts = explode('_',$nitem);
			if(count($parts) > 2 && strtolower($parts[1]) == 'module')
				$subfolder = '';
			
			$folder .= '/';
			$nitem = substr($nitem,strlen($folder));
		}
		
		$nitem = str_replace('_','/',$nitem);
		$nitem = strtolower($nitem);
		$nitem .= '.php';
		$path = FWS_Path::server_app().$folder.$subfolder.$nitem;
		
		if(is_file($path))
		{
			include($path);
			return true;
		}
	}
	
	return false;
}
?>