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

// define fwspath for init.php
if(!defined('FWS_PATH'))
	define('FWS_PATH',BS_PATH.BS_FWS_PATH);

// init the framework
include_once(FWS_PATH.'init.php');

// set the path
FWS_Path::set_server_app(BS_PATH);
FWS_Path::set_client_app(BS_PATH);

// init boardsolution
include_once(BS_PATH.'src/init.php');

$input = FWS_Props::get()->input();
$pages = array('navi','content','frameset');
$page = $input->correct_var('page','get',FWS_Input::IDENTIFIER,$pages,'frameset');

$class = 'BS_ACP_Document_'.$page;
if(class_exists($class))
{
	$doc = new $class();
	FWS_Props::get()->set_doc($doc);
	echo $doc->render();
}
else
	FWS_Helper::error('The class "'.$class.'" does not exist!');
?>