<?php
/**
 * The file for the frontend which may be called by the browser
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	main
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

// we are in the frontend
define('BS_FRONTEND',true);

// Please define the path to Boardsolution here. It should be a relative one starting from the
// file that includes the index.php to the folder of Boardsolution.
// By default (no include, the index.php is directly called in the browser) the path should be 
// empty ('').
// NOTE: it has to end with a '/' if it is not empty
define('BS_PATH','');

// check if the calculated path is correct
if(!is_file(BS_PATH.'config/general.php'))
{
	die(
		'Der angegebene Pfad (BS_PATH in der index.php) scheint falsch zu sein. Die
		"config/general.php" konnte nicht gefunden werden.<br />
		<br />
		The specified path (BS_PATH in the index.php) seems to be wrong. The "config/general.php"
		could not be found.'
	);
}

// Not yet installed?
if(!is_file(BS_PATH.'config/mysql.php'))
{
	header('Location: '.BS_PATH.'install.php');
	exit;
}

// does the install.php exist?
if(is_file(BS_PATH.'install.php'))
{
	die(
		'<center><b>Bitte l&ouml;schen Sie zun&auml;chst die install.php!<br />
			Please delete the install.php first!</b></center>'
	);
}

include_once(BS_PATH.'config/userdef.php');

// define fwspath for init.php
if(!defined('FWS_PATH'))
	define('FWS_PATH',BS_PATH.BS_FWS_PATH);

// init the framework
include_once(FWS_PATH.'init.php');

// set the path
FWS_Path::set_server_app(BS_PATH);
// TODO change!
if(defined('_JEXEC'))
{
	FWS_Path::set_client_app(JURI::base(true).'/bs/');
	FWS_Path::set_client_fw(JURI::base(true).'/bs/fws/');
}
else
	FWS_Path::set_client_app(BS_PATH);

// init boardsolution
include_once(BS_PATH.'src/init.php');

// TODO remove!
if(defined('_JEXEC'))
{
	jimport('scso.community');
	BS_Community_Manager::get_instance()->register_export(new BS_ComExport());
}

// show the page
$doc = new BS_Front_Document();
FWS_Props::get()->set_doc($doc);
echo $doc->render();
return $doc;
?>