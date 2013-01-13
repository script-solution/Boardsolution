<?php
/**
 * Contains the autoloader
 * 
 * @package			Boardsolution
 * @subpackage	src
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