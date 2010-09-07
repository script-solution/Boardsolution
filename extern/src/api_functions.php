<?php
/**
 * Contains some functions for the API
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	extern.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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