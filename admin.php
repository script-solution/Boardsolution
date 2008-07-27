<?php
/**
 * The file for the adminarea which may be called by the browser
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	main
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

// we are in the ACP
define('BS_ACP',true);

define('BS_PATH','');

include_once(BS_PATH.'config/userdef.php');

// define libpath for init.php
if(!defined('PLIB_PATH'))
	define('PLIB_PATH',BS_PATH.BS_LIB_PATH);

// init the library
include_once(PLIB_PATH.'init.php');

// set the path
PLIB_Path::set_server_app(BS_PATH);
PLIB_Path::set_client_app(BS_PATH);

// init boardsolution
include_once(BS_PATH.'src/init.php');

$input = PLIB_Props::get()->input();
$pages = array('navi','content','frameset');
$page = $input->correct_var('page','get',PLIB_Input::IDENTIFIER,$pages,'frameset');

$class = 'BS_ACP_Page_'.$page;
if(class_exists($class))
{
	$page = new $class();
	echo $page->render();
}
else
	PLIB_Helper::error('The class "'.$class.'" does not exist!');
?>