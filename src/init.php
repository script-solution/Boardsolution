<?php
/**
 * Contains the general init-code for all entry-points of Boardsolution
 * 
 * @package			Boardsolution
 * @subpackage	src
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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