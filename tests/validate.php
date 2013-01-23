<?php
/**
 * A tool to validate various pages of Boardsolution
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

define('TYPE_GUEST',	1);
define('TYPE_LOGGEDIN',	2);

// CLI or webserver?
define('LINE_WRAP',PHP_SAPI == 'cli' ? "\n" : '<br />');
define('INDENT',PHP_SAPI == 'cli' ? "\t" : '&nbsp;&nbsp;&nbsp;&nbsp;');
define('SESS_ID','1234-5678');
define('USER_ID',1);

define('BS_PATH','../');
include_once(BS_PATH.'extern/bs_api.php');

$pages = array(
	'index.php?action=login'						=> TYPE_GUEST,
	'index.php?action=forums'						=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=portal'						=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=memberlist'					=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=linklist'						=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=stats'						=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=faq'							=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=calendar'						=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=search'						=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userdetails&id='.USER_ID		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=infos'		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=signature'	=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=config'		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=avatars'		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=chgpw'		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=forums'		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=topics'		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=pmoverview'	=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=pminbox'		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=pmoutbox'		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=pmbanlist'	=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=pmsearch'		=> TYPE_GUEST | TYPE_LOGGEDIN,
	'index.php?action=userprofile&sub=pmcompose'	=> TYPE_GUEST | TYPE_LOGGEDIN,
);

$types = array(
	TYPE_GUEST			=> 'guest',
	TYPE_LOGGEDIN		=> 'user',
);

$ignores = array(
	'Warning: <table> lacks "summary" attribute',
	'Warning: <input> proprietary attribute "autocomplete"',
);

function login()
{
	BS_DAO::get_sessions()->create(array(
		'session_id' => SESS_ID,
		'user_id' => USER_ID,
		'user_ip' => '127.0.0.1',
		'date' => time(),
		'location' => '',
		'user_agent' => '',
		'session_data' => ''
	));
}

function logout()
{
	BS_DAO::get_sessions()->delete_by_sids(array(SESS_ID));
}

function is_ignored($line)
{
	global $ignores;
	foreach($ignores as $i)
	{
		if(strstr($line,$i) !== false)
			return true;
	}
	return false;
}

function validate($url,$type)
{
	global $types;
	$lines = array();
	if(PHP_SAPI == 'cli')
		echo 'Validating "'.$url.'" as '.$types[$type].LINE_WRAP;
	else
		echo 'Validating "<a href="'.$url.'">'.$url.'</a>" as '.$types[$type].LINE_WRAP;
	exec('wget -q -O - '.escapeshellarg($url).' | tidy -e -q 2>&1',$lines);
	foreach($lines as $line)
	{
		if(!is_ignored($line))
			echo INDENT.$line.LINE_WRAP;
	}
	ob_flush();
}

$cfg = FWS_Props::get()->cfg();
foreach($pages as $url => $type)
{
	$url = $cfg['board_url'].'/'.$url;
	if($type & TYPE_GUEST)
		validate($url,TYPE_GUEST);
	if($type & TYPE_LOGGEDIN)
	{
		login();
		validate($url.'&'.BS_URL_SID.'='.SESS_ID,TYPE_LOGGEDIN);
		logout();
	}
}
?>
