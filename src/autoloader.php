<?php
/**
 * Contains the autoloader
 *
 * @version			$Id: autoloader.php 543 2008-04-10 07:32:51Z nasmussen $
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
	if(PLIB_String::starts_with($item,'BS_'))
	{
		$nitem = PLIB_String::substr($item,3);
		$folder = PLIB_String::strtolower(strtok($nitem,'_'));
		$subfolder = 'src/';
		if(!in_array($folder,array('front','acp','install','dba','extern')))
			$folder = '';
		else
		{
			// allow includes in the module-folders
			$parts = explode('_',$nitem);
			if(count($parts) > 2 && PLIB_String::strtolower($parts[1]) == 'module')
				$subfolder = '';
			
			$folder .= '/';
			$nitem = PLIB_String::substr($nitem,PLIB_String::strlen($folder));
		}
		
		$nitem = str_replace('_','/',$nitem);
		$nitem = PLIB_String::strtolower($nitem);
		$nitem .= '.php';
		$path = PLIB_Path::inner().$folder.$subfolder.$nitem;
		
		if(is_file($path))
		{
			include($path);
			return true;
		}
	}
	
	return false;
}
?>