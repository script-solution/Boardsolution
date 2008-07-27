<?php
/**
 * Contains the general init-code for all entry-points of Boardsolution
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

// register our autoloader
include_once(BS_PATH.'src/autoloader.php');
PLIB_AutoLoader::register_loader('BS_Autoloader');

// include the files that we need at the very beginning
include_once(BS_PATH.'config/mysql.php');
include_once(BS_PATH.'config/general.php');
include_once(BS_PATH.'config/community.php');
include_once(BS_PATH.'src/props.php');

// set the accessor and loader for boardsolution
$accessor = new BS_PropAccessor();
$accessor->set_loader(new BS_PropLoader());
PLIB_Props::set_accessor($accessor);

BS_Front_Action_Base::load_actions();

// start profiler
$profiler = PLIB_Props::get()->profiler();
$profiler->start();

// init the session-stuff
$sessions = PLIB_Props::get()->sessions();
$user = PLIB_Props::get()->user();

// disable cookies in the ACP
if(defined('BS_ACP'))
	$user->set_use_cookies(false);

$user->init();
$sessions->garbage_collection();
?>