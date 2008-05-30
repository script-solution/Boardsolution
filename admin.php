<?php
/**
 * The file for the adminarea which may be called by the browser
 * 
 * @version			$Id: admin.php 745 2008-05-24 15:11:47Z nasmussen $
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
PLIB_AutoLoader::register_loader('BS_autoloader');

$input = PLIB_Input::get_instance();
$pages = array('navi','content','frameset');
$page = $input->correct_var('page','get',PLIB_Input::IDENTIFIER,$pages,'frameset');

$class = 'BS_ACP_Page_'.$page;
if(class_exists($class))
	new $class();
else
	PLIB_Helper::error('The class "'.$class.'" does not exist!');
?>