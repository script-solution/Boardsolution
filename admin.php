<?php
/**
 * The file for the adminarea which may be called by the browser
 * 
 * @package			Boardsolution
 * @subpackage	main
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