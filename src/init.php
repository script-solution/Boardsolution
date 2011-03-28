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
FWS_AutoLoader::register_loader('BS_Autoloader');

// include the files that we need at the very beginning
include_once(BS_PATH.'config/mysql.php');
include_once(BS_PATH.'config/general.php');
include_once(BS_PATH.'src/props.php');

// set the accessor and loader for boardsolution
$accessor = new BS_PropAccessor();
$accessor->set_loader(new BS_PropLoader());
FWS_Props::set_accessor($accessor);

// take care of other charsets
FWS_String::set_use_mb_functions(function_exists('mb_strlen'),BS_HTML_CHARSET);

BS_Front_Action_Base::load_actions();

// set our error-logger and allowed-files-listener
$e = FWS_Error_Handler::get_instance();
$e->add_allowedfiles_listener(new BS_Error_AllowedFiles());
$e->set_logger(new BS_Error_Logger());
if(PHP_SAPI != 'cli')
	$e->set_output_handler(new FWS_Error_Output_Default(BS_ERRORS_SHOW_CALLTRACE,BS_ERRORS_SHOW_BBCODE));

// init the session-stuff
$sessions = FWS_Props::get()->sessions();
$user = FWS_Props::get()->user();

// disable cookies in the ACP
if(defined('BS_ACP'))
	$user->set_use_cookies(false);

$user->init();
$sessions->garbage_collection();
?>