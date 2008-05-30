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
 * @param string $path the path to BS
 * @param string $name the name of the module
 * @param array $params an array with parameters to pass to the module
 * @return mixed the instance if successfull, otherwise false
 */
function BS_API_get_module($name,$params = null)
{
	if(file_exists(PLIB_Path::inner().'extern/modules/'.$name.'.php'))
	{
		include_once(PLIB_Path::inner().'extern/modules/'.$name.'.php');
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
	if($handle = opendir(PLIB_Path::inner().'extern/modules'))
	{
		while($file = readdir($handle))
		{
			if($file != '.' && $file != '..' && PLIB_FileUtils::get_extension($file) == 'php')
			{
				include_once(PLIB_Path::inner().'extern/modules/'.$file);
				$className = PLIB_String::substr($file,0,PLIB_String::strrpos($file,'.'));
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