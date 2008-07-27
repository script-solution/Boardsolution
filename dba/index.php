<?php
/**
 * The index-page for the DB-backup-script
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

define('BS_PATH','../');

include_once(BS_PATH.'config/userdef.php');
include_once(BS_PATH.'config/dbbackup.php');

// define libpath for init.php
if(!defined('PLIB_PATH'))
	define('PLIB_PATH',BS_PATH.BS_LIB_PATH);

// init the library
include_once(PLIB_PATH.'init.php');

// set the path
PLIB_Path::set_server_app(BS_PATH);
PLIB_Path::set_client_app(BS_PATH);
// Note that we don't need the outer-path here

// init boardsolution
include_once(BS_PATH.'src/autoloader.php');
PLIB_AutoLoader::register_loader('BS_Autoloader');

// include the files that we need at the very beginning
include_once(BS_PATH.'config/mysql.php');
include_once(BS_PATH.'config/general.php');
include_once(BS_PATH.'src/props.php');

// set the accessor and loader for boardsolution
$accessor = new BS_DBA_PropAccessor();
$accessor->set_loader(new BS_DBA_PropLoader());
PLIB_Props::set_accessor($accessor);

BS_Front_Action_Base::load_actions();

// start profiler
$profiler = PLIB_Props::get()->profiler();
$profiler->start();

// init the session-stuff
$sessions = PLIB_Props::get()->sessions();
$user = PLIB_Props::get()->user();

$user->init();
$sessions->garbage_collection();

$page = new BS_DBA_Page();
echo $page->render();
?>