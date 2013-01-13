<?php
/**
 * Contains some functions for the API
 * 
 * @package			Boardsolution
 * @subpackage	extern.src
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
 * Loads the API-module with given name and returns an instance
 * 
 * @param string $name the name of the module
 * @param array $params an array with parameters to pass to the module
 * @return BS_API_Module|bool the instance if successfull, otherwise false
 */
function BS_API_get_module($name,$params = null)
{
	if(file_exists(FWS_Path::server_app().'extern/modules/'.$name.'.php'))
	{
		include_once(FWS_Path::server_app().'extern/modules/'.$name.'.php');
		$class = 'BS_API_Module_'.$name;
		if(class_exists($class))
		{
			$c = new $class();
			
			if($params !== null)
				$c->run($params);
			else
				$c->run();
			
			return $c;
		}
	}
	
	return false;
}

/**
 * Creates a list with all available modules
 * 
 * @return array an array with all modules
 */
function BS_API_get_available_modules()
{
	$modules = array();
	if($handle = opendir(FWS_Path::server_app().'extern/modules'))
	{
		while($file = readdir($handle))
		{
			if($file != '.' && $file != '..' && FWS_FileUtils::get_extension($file) == 'php')
			{
				include_once(FWS_Path::server_app().'extern/modules/'.$file);
				$className = FWS_String::substr($file,0,FWS_String::strrpos($file,'.'));
				$class = 'BS_API_Module_'.$className;
				if(class_exists($class))
					$modules[] = $className;
			}
		}
		closedir($handle);
	}
	
	return $modules;
}
?>