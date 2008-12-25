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

// init the session-stuff
$sessions = FWS_Props::get()->sessions();
$user = FWS_Props::get()->user();

// disable cookies in the ACP
if(defined('BS_ACP'))
	$user->set_use_cookies(false);

$user->init();
$sessions->garbage_collection();

// TODO remove!
/*$cm = BS_Community_Manager::get_instance();
$cm->disable_registration();
$cm->disable_resend_act();
$cm->disable_send_pw();
$cm->disable_user_management();
$cm->set_register_url('../../joomlabs/index.php?option=com_user&amp;task=register');
$cm->set_resend_act_url('');
$cm->set_send_pw_url('../../joomlabs/index.php?option=com_user&amp;view=reset');

if(defined('_JEXEC'))
	BS_Community_Manager::get_instance()->add_login_listener(new BS_JLoginListener());*/
?>