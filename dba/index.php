<?php
/**
 * The index-page for the DB-backup-script
 * 
 * @package			Boardsolution
 * @subpackage	dba
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

define('BS_PATH','../');

include_once(BS_PATH.'config/userdef.php');
include_once(BS_PATH.'config/dbbackup.php');

// define fwspath for init.php
if(!defined('FWS_PATH'))
	define('FWS_PATH',BS_PATH.BS_FWS_PATH);

// init the framework
include_once(FWS_PATH.'init.php');

// set the path
FWS_Path::set_server_app(BS_PATH);
FWS_Path::set_client_app(BS_PATH);
// Note that we don't need the outer-path here

// init boardsolution
include_once(BS_PATH.'src/autoloader.php');
FWS_AutoLoader::register_loader('BS_Autoloader');

// include the files that we need at the very beginning
include_once(BS_PATH.'config/mysql.php');
include_once(BS_PATH.'config/general.php');
include_once(BS_PATH.'src/props.php');

// set the accessor and loader for boardsolution
$accessor = new BS_DBA_PropAccessor();
$accessor->set_loader(new BS_DBA_PropLoader());
FWS_Props::set_accessor($accessor);

BS_Front_Action_Base::load_actions();

// init the session-stuff
$sessions = FWS_Props::get()->sessions();
$user = FWS_Props::get()->user();

$user->init();
$sessions->garbage_collection();

$doc = FWS_Props::get()->doc();
echo $doc->render();
?>