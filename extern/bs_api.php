<?php
/**
 * Includes all important files and instantiates the BS_Base-class
 * 
 * @package			Boardsolution
 * @subpackage	extern
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

if(!defined('BS_PATH'))
	die('Please set BS_PATH first!');

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
$cfg = FWS_Props::get()->cfg();
$locale = FWS_Props::get()->locale();
FWS_Path::set_outer($cfg['board_url'].'/');
FWS_Error_Handler::get_instance()->set_logger(new BS_Error_Logger());
$locale->add_language_file('index');

// load extern-API stuff
include_once(BS_PATH.'extern/src/api_functions.php');
include_once(BS_PATH.'extern/src/api_module.php');

ob_start();

/**
 * Should be called when you're done so that the db-connection can be closed, the session-data
 * can be written do db and so on
 */
function BS_finish()
{
	$db = FWS_Props::get()->db();
	$sessions = FWS_Props::get()->sessions();
	
	$sessions->finalize();
	$db->disconnect();
	
	ob_end_flush();
}
?>