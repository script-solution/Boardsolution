<?php
/**
 * Refreshes the db-cache. This script may be usefull if the cache is broken and you can't refresh
 * it in the adminarea because you are unable to get there.
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

define('LINE_WRAP',PHP_SAPI == 'cli' ? "\n" : '<br />');

define('BS_PATH','../');
include(BS_PATH.'extern/bs_api.php');

echo "Refreshing cache...";
$cache = FWS_Props::get()->cache();

// at first, make sure that all entries are in the db. if they're not, the cache won't be regenerated
$db = FWS_Props::get()->db();
$entries = array(
	'banlist','intern','languages','moderators','themes','user_groups','user_ranks','config',
	'user_fields','stats','tasks','acp_access','bots'
);
$refresh = false;
foreach($entries as $name)
{
	if($cache->get_cache($name) === null)
	{
		$db->insert(BS_TB_CACHE,array('table_name' => $name));
		$refresh = true;
	}
}
if($refresh)
{
	FWS_Props::get()->reload('cache');
	$cache = FWS_Props::get()->cache();
}

$cache->refresh_all();
echo "DONE".LINE_WRAP;

BS_finish();
?>