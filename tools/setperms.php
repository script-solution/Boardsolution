<?php
/**
 * Sets all required file/folder-permissions. Since there are quite a few permissions to set
 * 
 * @package			Boardsolution
 * @subpackage	tools
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
