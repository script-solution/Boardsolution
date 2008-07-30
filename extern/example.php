<?php
/**
 * An example to demonstrate the features and usage of the BS-extern-API
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	extern
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

// define the path to the root of boardsolution (relative)
// this is required for the bs_api.php
define('BS_PATH','../');

// include bs_api.php, which loads all required stuff
include_once(BS_PATH.'extern/bs_api.php');

// we have to set the charset here to force the browser to use the selected charset
header('Content-Type: text/html; charset='.BS_HTML_CHARSET);

// now add the html-header
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Boardsolution - Extern API</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo BS_HTML_CHARSET; ?>" />
</head>
<body>
<?php

######################## OTHER ########################

$user = FWS_Props::get()->user();

echo '<h2>Demonstration der BS-Extern-API</h2>'."\n";
// the API logs the user in if the autologin-cookies are set (and valid)
// therefore we have also access to the username, unread topics and other things which
// belong to the user

// check if the user is logged in?
if($user->is_loggedin())
	echo 'Eingeloggt als <b>'.$user->get_user_name().'</b>.';
else
	echo 'Nicht eingeloggt.';
echo '<br />';

// print the available modules (just for fun :))
echo '<br />';
echo '<b>Verf&uuml;gbare Module:</b> ';
echo implode(', ',BS_API_get_available_modules()).'.'."\n";
echo '<br />';
echo '<br />';

######################## GENERAL STATS ########################

// at first we have to request the module we want to use
// in this case it is the "general_stats" module which provides us access to many statistics
// of the board such as the number of forums, the last post and other things
// the name of the module is simply the filename in extern/modules/ without file-extension
$stats = BS_API_get_module('general_stats');

echo '<hr />'."\n";
echo '<b>Allgemeine Statistiken:</b><br />';
// print he number of forums, topics, posts and user
echo 'Das Forum hat momentan <b>'.$stats->forum_count.'</b> Foren,';
echo ' <b>'.$stats->topic_count.'</b> Themen, <b>'.$stats->post_count.'</b>'."\n";
echo ' Beitr&auml;ge und <b>'.$stats->registered_user.'</b> registrierte User.'."\n"; 
echo '<br />'."\n";
echo '<br />'."\n";
// print last login; we use the function BS_API_get_date() to print it in a human-readable format
// $stats->last_login_time is just a timestamp which contains the number of seconds since 1.1.1970, 0:00
echo 'Der letzte Login war <u>'.FWS_Date::get_date($stats->last_login_time).'</u>'."\n";
echo ' und der letzte Beitrag <u>'.FWS_Date::get_date($stats->last_post_time).'</u>.'."\n";
echo '<br />'."\n";
echo '<br />'."\n";
// print the newest member
echo 'Das neueste Mitglied ist: ';
if($stats->newest_member_id)
{
	$murl = BS_URL::get_frontend_url(
		'&amp;'.BS_URL_ACTION.'=userdetails&amp;'.BS_URL_ID.'='.$stats->newest_member_id
	);
	echo '<a href="'.$murl.'">'.$stats->newest_member_name.'</a>';
}
else
	echo '<i>n/a</i>';
echo '<br />'."\n";
echo '<br />'."\n";


######################## ONLINE USER ########################

// request module "online_user" to get the currently online user in the board
$online = BS_API_get_module('online_user');

// collect the registered user
$i = 0;
$registered = '';
foreach($online->online_user as $reg)
{
	// build url to the user-details
	$murl = BS_URL::get_frontend_url(
		'&amp;'.BS_URL_ACTION.'=userdetails&amp;'.BS_URL_ID.'='.$reg['id']
	);
	$registered .= '<a title="'.$reg['location'].'" href="'.$murl.'">'.$reg['name'].'</a>';
	if($i < count($online->online_user) - 1)
		$registered .= ', ';
	$i++;
}

// print who is online
echo '<hr />'."\n";
echo '<b>Momentan online ('.$online->total_online.'):</b>'."\n";
echo '<ul>'."\n";
// number of registered user; $online->online_user is an array, therefore we use count() to get
// the number of elements in it
echo '	<li>'.count($online->online_user).' Registrierte: '.$registered.'</li>'."\n";
// guests
echo '	<li>'.$online->online_guest_num.' G&auml;ste</li>'."\n";
// ghosts (user who have the ghostmode activated in their profile
echo '	<li>'.$online->online_ghost_num.' Versteckte</li>'."\n";
// bots; is also an array. in this case it contains just the names, theirfore we can simply
// use implode() to print the bots separated by ", "
echo '	<li>'.count($online->online_bots).' Bots: '.implode(', ',$online->online_bots).'</li>'."\n";
echo '</ul>'."\n";
echo '<br />'."\n";


######################## LATEST TOPICS ########################

// request "latest_topics" module
$topics = BS_API_get_module('latest_topics');

// print header
echo '<hr />'."\n";
echo '<b>Die aktuellen Themen:</b><br /><br />';
echo '<table cellpadding="2" cellspacing="1" border="1">'."\n";
echo '	<tr>'."\n";
echo '		<td style="font-weight: bold;" width="40%">Name</td>'."\n";
echo '		<td style="font-weight: bold;" width="10%" align="center">Antworten</td>'."\n";
echo '		<td style="font-weight: bold;" width="25%">Themener&ouml;ffnung</td>'."\n";
echo '		<td style="font-weight: bold;" width="25%">Letzter Beitrag</td>'."\n";
echo '	</tr>'."\n";

// print the topics
foreach($topics->latest_topics as $topic)
{
	// the topic-creation-date (its a timestamp like nearly all dates stored by Boardsolution)
	$creation = FWS_Date::get_date($topic['creation_date']);
	// topic-creation by a registered user?
	if($topic['creation_user_id'] > 0)
	{
		// build url to the user who created the topic
		$murl = BS_URL::get_frontend_url(
			'&amp;'.BS_URL_ACTION.'=userdetails&amp;'.BS_URL_ID.'='.$topic['creation_user_id']
		);
		$creation .= '<br />Von: <a href="'.$murl.'">'.$topic['creation_user_name'].'</a>';
	}
	// or by a guest?
	else
		$creation .= '<br />Von: '.$topic['creation_user_name'];
	
	// last post date
	$lastpost = FWS_Date::get_date($topic['lastpost_date']);
	// last post by a registered user?
	if($topic['lastpost_user_id'] > 0)
	{
		$murl = BS_URL::get_frontend_url(
			'&amp;'.BS_URL_ACTION.'=userdetails&amp;'.BS_URL_ID.'='.$topic['lastpost_user_id']
		);
		$lastpost .= '<br />Von: <a href="'.$murl.'">'.$topic['lastpost_user_name'].'</a>';
	}
	// or by a guest
	else
		$lastpost .= '<br />Von: '.$topic['lastpost_user_name'];
	
	// add link to last post
	$lastpost_url = BS_URL::get_frontend_url(
		'&amp;'.BS_URL_ACTION.'=redirect&amp;'.BS_URL_LOC.'=show_post&amp;'
			.BS_URL_ID.'='.$topic['lastpost_id']
	);
	$lastpost .= ' <a href="'.$lastpost_url.'">&raquo;&raquo;</a>';
	
	// build url to topic
	$turl = BS_URL::get_frontend_url(
		'&amp;'.BS_URL_ACTION.'=posts&amp;'.BS_URL_FID.'='.$topic['forum_id']
			.'&amp;'.BS_URL_TID.'='.$topic['id']
	);
	// build url to forum
	$furl = BS_URL::get_frontend_url(
		'&amp;'.BS_URL_ACTION.'=topics&amp;'.BS_URL_FID.'='.$topic['forum_id']
	);
	$forum = '<a style="font-size: 11px;" href="'.$furl.'">'.$topic['forum_name'].'</a>';
	echo '	<tr>'."\n";
	// print topic-name
	echo '		<td><a href="'.$turl.'">'.$topic['name'].'</a>';
	// print forum-name
	echo '		<br /><span style="font-size: 11px;">Forum: '.$forum.'</span></td>'."\n";
	// print number of replies
	echo '		<td align="center">'.$topic['replies'].'</td>'."\n";
	// and creation and last post
	echo '		<td>'.$creation.'</td>'."\n";
	echo '		<td>'.$lastpost.'</td>'."\n";
	echo '	</tr>'."\n";
}

echo '</table>'."\n";


######################## UNREAD ########################

echo '<hr />'."\n";
echo '<b>Ungelesene Themen / PMs:</b><br />';

// request "unread"-module
$unread = BS_API_get_module('unread');

echo '<ul>'."\n";
echo '	<li>Ungelesene Foren: ';

// do we have some unread-forums (unread topics in it) ?
if(count($unread->unread_forums) > 0)
{
	// so print them
	for($i = 0,$len = count($unread->unread_forums);$i < $len;$i++)
	{
		// build the url to the forum
		$murl = BS_URL::get_frontend_url(
			'&amp;'.BS_URL_ACTION.'=topics&amp;'.BS_URL_ID.'='.$unread->unread_forums[$i]
		);
		echo '<a href="'.$murl.'">'.$bs->forums->get_forum_name($unread->unread_forums[$i]).'</a>';
		// print separator if not the last one
		if($i < $len - 1)
			echo ', ';
	}
}
// ok, we don't have unread forums
else
	echo '<i>Keine</i>';

echo '</li>'."\n";
echo '	<li>Ungelesene Themen:'."\n";
// collect unread topic-ids for the sql-query
$topic_ids = array();
foreach($unread->unread_topics as $utopic)
	$topic_ids[] = $utopic['id'];

// have we unread-topics?
if(count($topic_ids) > 0)
{
	echo '	<ul>'."\n";
	// request the topic-names from the database
	foreach(BS_DAO::get_topics()->get_by_ids($topic_ids) as $utdata)
	{
		// build topic-url
		$murl = BS_URL::get_frontend_url(
			'&amp;'.BS_URL_ACTION.'=posts&amp;'.BS_URL_FID.'='.$utdata['fid']
				.'&amp;'.BS_URL_TID.'='.$utdata['id']
		);
		echo '		<li><a href="'.$murl.'">'.$utdata['name'].'</a></li>'."\n";
	}
	echo '	</ul>'."\n";
}
// no unread topics
else
	echo '<i>Keine</i>';

// print the number of unread pms
echo '	</li>'."\n";
echo '	<li>Ungelesene PMs: '.$unread->unread_pms.'</li>'."\n";
echo '</ul>'."\n";


######################## EVENTS ########################

echo '<hr />'."\n";
echo '<b>Termine in den n&auml;chsten 5 Tagen:</b><br />';

// request "events"-module
// we want to get the events in the next 5 days, therefore we pass the parameter 'event_timeout'
// to the module with 5 days in seconds
$events = BS_API_get_module('events',array('event_timeout' => 3600 * 24 * 5));

// are there any events?
if(count($events->events) > 0)
{
	foreach($events->events as $i => $edata)
	{
		// is it an event in the calendar?
		if($edata['topic_id'] == 0)
		{
			$murl = BS_URL::get_frontend_url(
				'&amp;'.BS_URL_ACTION.'=calendar&amp;'.BS_URL_MODE.'=event_detail&amp;'
					.BS_URL_ID.'='.$edata['id']
			);
		}
		// or an event in a forum?
		else
		{
			$murl = BS_URL::get_frontend_url(
				'&amp;'.BS_URL_ACTION.'=posts&amp;'.BS_URL_FID.'='.$edata['forum_id']
				.'&amp;'.BS_URL_TID.'='.$edata['topic_id']
			);
		}
		
		// print the link to the topic / event-details and append the date of the event
		echo '<a href="'.$murl.'">'.$edata['title'].'</a> ('.FWS_Date::get_date($edata['begin'],false).')';
		
		// the separator
		if($i < count($events->events) - 1)
			echo ', ';
	}
}
// no events
else
	echo '<i>Keine</i>';

echo '<hr />'."\n";
echo '<b>Heutige Geburtstage:</b><br />';

// are there any birthdays today?
if(count($events->birthdays) > 0)
{
	foreach($events->birthdays as $i => $bdata)
	{
		// build userdetails-url
		$murl = BS_URL::get_frontend_url(
			'&amp;'.BS_URL_ACTION.'=userdetails&amp;'.BS_URL_ID.'='.$bdata['id']
		);
		// print link to user
		echo '<a href="'.$murl.'">'.$bdata['user_name'].'</a>';
		
		// $bdata['birthday'] has the format YYYY-MM-DD. therefore we split it at "-" to get the parts of it
		$parts = explode('-',$bdata['add_birthday']);
		// calculate the age of the user
		echo ' ('.(FWS_Date::get_formated_date('Y') - $parts[0]).')';
		
		// print separator
		if($i < count($events->birthdays) - 1)
			echo ', ';
	}
}
// no birthdays today
else
	echo '<i>Keine</i>';

?>
</body>
</html>
<?php
// IMPORTANT!
BS_finish();
?>