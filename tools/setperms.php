<?php
/**
 * Sets all required file/folder-permissions. Since there are quite a few permissions to set
 * this script may save some work. But note that it is not always possible to use it because
 * of course the script has to have to permission to change file-/folder-permissions. That means
 * in most cases it won't be possible to call this script in your browser because typically the
 * files and folders are owned by the FTP-user and the webserver-user can't change the permissions
 * of them.
 * If you have access to the command-line of your server you may use it like the following:
 * 	# cd path/to/bs/tools
 * 	# php setperms.php
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	tools
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

// Wenn Sie die folgenden 3 Zeilen entfernen, koennen Sie das Script im Browser aufrufen.
// Das ist allerdings fuer jeden moeglich. Daher sollten Sie die Zeilen AUF JEDEN FALL wieder
// hinzufuegen, sobald Sie fertig sind!
// Please remove the following 3 lines so that you can call the script in the browser.
// This will be possible for everyone. Therefore you should re-add it IN EVERY CASE
// as soon as you're ready!
die('Bitte &ouml;ffnen Sie diese Datei via FTP und entfernen Sie diese Zeile um das Script
	benutzen zu k&ouml;nnen!<br /><br />
	Please open this file via FTP and remove this line to be able to use this script!');

define('BS_PATH','../');
include(BS_PATH.'config/userdef.php');
define('FWS_PATH',BS_PATH.BS_FWS_PATH);
include(FWS_PATH.'init.php');

// CLI or webserver?
define('LINE_WRAP',PHP_SAPI == 'cli' ? "\n" : '<br />');

$folders = array(
	'cache',
	'config',
	'dba',
	'dba/backups',
	'images/avatars',
	'images/smileys',
	'uploads',
);

// folders
foreach($folders as $folder)
	setPerms(BS_PATH.$folder,0777);

// theme-files
foreach(FWS_FileUtils::get_list(BS_PATH.'themes') as $theme)
{
	if(is_dir(BS_PATH.'themes/'.$theme) && $theme[0] != '.')
	{
		setPerms(BS_PATH.'themes/'.$theme.'/basic.css',0666);
		foreach(FWS_FileUtils::get_list(BS_PATH.'themes/'.$theme.'/templates') as $tpl)
		{
			if(FWS_String::ends_with($tpl,'.htm'))
				setPerms(BS_PATH.'themes/'.$theme.'/templates/'.$tpl,0666);
		}
	}
}

/**
 * @param string $file the file
 * @param int $val the permission to set
 */
function setPerms($file,$val)
{
	if(@chmod($file,$val))
		printf("Changed permissions of '%s' to %o%s",$file,$val,LINE_WRAP);
	else
		printf("Unable to change permissions of '%s'%s",$file,LINE_WRAP);
}
?>
