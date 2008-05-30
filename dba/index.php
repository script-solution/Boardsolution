<?php
/**
 * The index-page for the DB-backup-script
 * 
 * @version			$Id: index.php 745 2008-05-24 15:11:47Z nasmussen $
 * @package			Boardsolution
 * @subpackage	dba
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

$bspath = '../';
include_once($bspath.'config/userdef.php');
include_once($bspath.'config/dbbackup.php');

// define libpath for init.php
define('PLIB_PATH',$bspath.BS_LIB_PATH);

// init the library
include_once(PLIB_PATH.'init.php');

// set the path
PLIB_Path::set_inner($bspath);
// Note that we don't need the outer-path here

// init the autoloader
include_once(PLIB_Path::inner().'src/autoloader.php');
PLIB_AutoLoader::register_loader('BS_Autoloader');

new BS_DBA_Page();
?>