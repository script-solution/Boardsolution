<?php
/**
 * Includes all important files and instantiates the BS_Base-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	extern
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

if(!defined('BS_PATH'))
	die('Please set BS_PATH first!');

include_once(BS_PATH.'config/userdef.php');

// define libpath for init.php
if(!defined('PLIB_PATH'))
	define('PLIB_PATH',BS_PATH.BS_LIB_PATH);

// init the library
include_once(PLIB_PATH.'init.php');

// set the path
PLIB_Path::set_inner(BS_PATH);

// init the autoloader
include_once(PLIB_Path::inner().'src/autoloader.php');
PLIB_AutoLoader::register_loader('BS_Autoloader');

// create document class
class BS_Extern_Document extends BS_Document
{
	public function finish()
	{
		$this->_finish();
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}

include_once(BS_PATH.'extern/src/api_functions.php');
include_once(BS_PATH.'extern/src/api_module.php');

$doc = new BS_Extern_Document();
// TODO keep that?
//register_shutdown_function(array($doc,'finish'));

return $doc;
?>