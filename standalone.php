<?php
/**
 * The file for the other actions than the frontend like for example popups or the activation-page
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	main
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

$bspath = '';
include_once($bspath.'config/userdef.php');

// define libpath for init.php
define('PLIB_PATH',BS_LIB_PATH);

// init the library
include_once(BS_LIB_PATH.'init.php');

// set the path
PLIB_Path::set_inner($bspath);

// init the autoloader
include_once(PLIB_Path::inner().'src/autoloader.php');
PLIB_AutoLoader::register_loader('BS_Autoloader');

// ok, now show the page
new BS_Page_Standalone();
?>