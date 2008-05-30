<?php
$LANG['installationtitle'] = 'Installation von '.BS_VERSION;
$LANG['available'] = 'Verf&uuml;gbar';
$LANG['notavailable'] = 'Nicht Verf&uuml;gbar';
$LANG['ok'] = 'OK';
$LANG['notok'] = 'Nicht OK';
$LANG['password'] = 'Passwort';
$LANG['database'] = 'Datenbank';
$LANG['refresh'] = 'Aktualisieren';
$LANG['next_message'] = 'N&auml;chste Nachricht';
$LANG['previous_message'] = 'Letzte Nachricht';
$LANG['edit_message'] = 'Bearbeiten';
$LANG['information'] = 'Information';
$LANG['position'] = 'Position';
$LANG['type'] = 'Typ';
$LANG['error_occurred'] = 'Folgende Angaben fehlen noch oder sind nicht korrekt';

$LANG['step_intro'] = 'Schritt 1: Wichtige Hinweise';
$LANG['step_type'] = 'Schritt 2: Installations-Typ';
$LANG['step_config'] = 'Schritt 3: Einstellungen';
$LANG['step_dbcheck'] = 'Schritt 4: &Uuml;berpr&uuml;fung der MySQL-Tabellen Struktur';
$LANG['step_process'] = 'Schritt 5: Installation';
$LANG['step6'] = 'Schritt 6: Neuberechnung der Nachrichten';
$LANG['step7'] = 'Schritt 7: Ung&uuml;ltige Nachrichten bearbeiten';

$LANG['step1_explain'] = '<span style="font-weight: bold; color: #FF0000; font-size: 13px;">Bitte lies Dir diese Hinweise durch um
sp&auml;teren Problemen und Fragen vorzubeugen!</span>';
$LANG['step2_explain'] = 'Falls Du schon Boardsolution v1.2x installiert hast, w&auml;hle hier bitte "Update".<br />
Wenn Du eine &auml;ltere Version installiert hast, z.B. Boardsolution v1.1x, musst Du Schrittweise "updaten". D.h. Du musst
zuerst Boardsolution v1.22 runterladen und dort die Installation durchf&uuml;hren und danach erst diese Version installieren.<br />
<br />
Anderenfalls muss eine "Neuinstallation" durchgef&uuml;hrt werden.';

$LANG['type_entry'] = 'Eintrag';
$LANG['type_comment'] = 'Kommentar';
$LANG['error_text'] = 'Fehler';
$LANG['edit_messages_success'] = 'Die Nachricht wurde erfolgreich editiert.';
$LANG['step7_success'] = 'Alle Nachrichten wurden editiert!';

$LANG['step1_message_changes_title'] = 'Nachrichten';
$LANG['step1_message_changes_text'] = 'Seit <b>Boardsolution v1.10</b> ist die <b>Speicherung und Anzeige von Nachrichten</b>
(Eintr&auml;ge und Kommentare) <b>anders geregelt</b> als bisher.<br />
Vorher wurde lediglich der vom User eingegebene Text in der Datenbank gespeichert und bei der Ausgabe
umgewandelt und angezeigt. Dies war sehr langsam, weshalb es jetzt anders abl&auml;uft.<br />
In dieser Version wird zus&auml;tzlich zu dem Text, welchen der User eingegeben hat, gleich der umgewandelte Text
in der Datenbank gespeichert. Das bedeutet, gleich nach dem Abschicken / Editieren einer Nachricht wird der Text
umgewandelt und das Resultat gespeichert.<br />
Dadurch k&ouml;nnen die <b>Nachrichten sehr viel schneller dargestellt</b> werden.<br />
<br />
Dies hat allerdings den <b>Nachteil</b>, dass <b>nicht dynamisch auf &Auml;nderungen</b> an den Smiley, Badwords oder anderen
Einstellungen, die die Nachrichten betreffen, <b>reagiert werden kann</b>.<br />
Aus dem Grund gibt es die M&ouml;glichkeit im Adminbereich alle Nachrichten neuzuberechnen. Dies kann unter
Wartung -> Verschiedenes -> Nachrichten aktualisieren durchgef&uuml;hrt werden.';

$LANG['step1_database_changes_title'] = '&Auml;nderungen an der Datenbank';
$LANG['step1_database_changes_text'] = 'Boardsolution speichert den Inhalt einiger MySQL-Tabellen
zus&auml;tzlich in einer Extra-Tabelle um den Inhalt schneller auslesen zu k&ouml;nnen. Dies betrifft die
Tabellen, die voraussichtlich nur wenig Inhalt enthalten und selten ver&auml;ndert werden.<br />
<b>Aus dem Grund solltest Du keine Manuellen &Auml;nderungen an der Datenbankstruktur vorgenehmen</b>,
sofern Du nicht genau wei&szlig;t was Du tust!<br />
<br />
Der sogenannte "DB-Cache" kann allerdings im Adminbereich neu berechnet werden.<br />
D.h. wenn Du Ver&auml;nderungen an der Datenbankstruktur durchf&uuml;hrst, solltest Du (je nach dem welche
Tabelle Du ge&auml;ndert hast) den Cache der Tabelle unter Wartung -> DB-Cache neu berechnen.<br />
<br />
Au&szlig;erdem h&auml;ngen viele MySQL-Tabellen zusammen. Zum Beispiel werden
in der Eintr&auml;ge-Tabelle die Anzahl der Kommentare gespeichert.<br />
<b>Auch aus dem Grund m&ouml;chte ich davon abraten in der Datenbank etwas zu &auml;ndern!</b>';

$LANG['step1_further_settings_title'] = 'Weitere Einstellungen';
$LANG['step1_further_settings_text'] = 'Zus&auml;tzlich zu den Einstellungen im Adminbereich gibt es einige
weitere Einstellungen, die in den Dateien <b>config/userdef.php</b>, <b>config/actions.php</b> und
<b>config/bbcode.php</b> bearbeitet werden k&ouml;nnen. Dabei handelt es
sich um <b>Detail-Einstellungen</b>, die f&uuml;r die "normale" Nutzung nicht unbedingt notwendig sind und
<b>prim&auml;r f&uuml;r erfahrenere Anwender</b> gedacht sind.<br />
<br />
Dort kann z.B. der <b>BBCode konfiguriert werden</b>. Durch die sehr flexible BBCode-Engine in Boardsolution kann
der BBCode komplett ver&auml;ndert und erweitert werden. Wie das genau geht, ist in der config/bbcode.php
(hoffentlich ausreichend) erkl&auml;rt.<br />
<br />
Daneben k&ouml;nnen <b>noch viele weitere Einstellungen</b> in den Dateien vorgenommen werden, auf die ich aber hier
nicht genauer eingehen m&ouml;chte.';

$LANG['writable'] = 'Beschreibbar';
$LANG['notwritable'] = 'Nicht beschreibbar';

$LANG['error']['phpversion'] = 'Du musst mindestens PHP-Version 4.1.0 besitzen';
$LANG['error']['mysql'] = 'Du musst mindestens MySQL 3.x besitzen';
$LANG['error']['chmod_cache'] = 'Stelle bitte die Zugriffsrechte von "cache" so ein, dass es beschreibbar ist (z.B. 0777).';
$LANG['error']['chmod_config'] = 'Stelle bitte die Zugriffsrechte von "config" so ein, dass es beschreibbar ist (z.B. 0777).';
$LANG['error']['chmod_config_community'] = 'Stelle bitte die Zugriffsrechte von "config/community.php" so ein, dass es beschreibbar ist (z.B. 0666).';
$LANG['error']['chmod_config_userdef'] = 'Stelle bitte die Zugriffsrechte von "config/userdef.php" so ein, dass es beschreibbar ist (z.B. 0666).';
$LANG['error']['chmod_themes'] = 'Stelle bitte die Zugriffsrechte von "themes/&lt;theme&gt;/style.css" und allen Dateien in "themes/default/templates" so ein, dass sie beschreibbar sind (z.B. 0666).';
$LANG['error']['chmod_themes_codes'] = array(
	1 => 'Die Zugriffsrechte von "themes/default/style.css" sind bisher nicht korrekt',
	2 => 'Die Zugriffsrechte einer Datei in "themes/default/templates" sind bisher nicht korrekt',
	3 => 'Das Verzeichnis "themes/default/templates" ist nicht lesbar'
);
$LANG['error']['chmod_smileys'] = 'Stelle bitte die Zugriffsrechte von "images/smileys" so ein, dass es beschreibbar ist (z.B. 0777).';
$LANG['error']['chmod_avatars'] = 'Stelle bitte die Zugriffsrechte von "images/avatars" so ein, dass es beschreibbar ist (z.B. 0777).';
$LANG['error']['chmod_uploads'] = 'Stelle bitte die Zugriffsrechte von "uploads" so ein, dass es beschreibbar ist (z.B. 0777).';
$LANG['error']['chmod_dbbackup'] = 'Stelle bitte die Zugriffsrechte von "dbbackup/backups" so ein, dass es beschreibbar ist (z.B. 0777).';
$LANG['error']['chmod_dbaaccess'] = 'Stelle bitte die Zugriffsrechte von "dbbackup" so ein, dass es beschreibbar ist (z.B. 0777).';
$LANG['error']['mysql_connect'] = '&Uuml;berpr&uuml;fe bitte "Host", "Login" und "Passwort" der MySQL-Einstellungen';
$LANG['error']['mysql_select_db'] = '&Uuml;berpr&uuml;fe bitte den Datenbanknamen';
$LANG['error']['admin_login'] = 'Bitte gib den Usernamen des Administrators an.';
$LANG['error']['admin_pw'] = 'Bitte gib das Passwort des Administrators an.';
$LANG['error']['admin_email'] = 'Bitte gib die Email-Adresse des Administrators an.';
$LANG['error']['board_url'] = 'Bitte gib den Board-Pfad an.';

$LANG['gd_description'] = 'Die GD-Library ist nur optional';

$LANG['voraussetzungenerfuellt'] = 'Alle Voraussetzungen f&uuml;r die Installation sind erf&uuml;llt.';
$LANG['noterfuellt'] = 'Es sind nicht alle Voraussetzungen erf&uuml;llt';

$LANG['back'] = 'Zur&uuml;ck';
$LANG['forward'] = 'Weiter';
$LANG['finish'] = 'Installieren';

$LANG['yes'] = 'Ja';
$LANG['no'] = 'Nein';
$LANG['admin_login'] = 'Admin - Login';
$LANG['admin_pw'] = 'Admin - Passwort';
$LANG['admin_email'] = 'Admin - Email';
$LANG['board_url'] = 'Board - URL';
$LANG['board_url_desc'] = 'Die absolute URL zu Deinem Board. D.h., wenn Boardsolution z.B. hier liegt: "http://www.domain.de/board/index.php", dann w&auml;re die URL: "http://www.domain.de/board"<br />
Wichtig ist, dass der letzte "/" nicht angegeben wird.';
$LANG['kindofinstall'] = 'Installations-Typ';
$LANG['fullinstall'] = 'Neuinstallation';
$LANG['update'] = 'Update';
$LANG['table_praefix'] = 'Tabellen-Pr&auml;fix';
$LANG['btn_update'] = 'Aktualisieren';

$LANG['important_tasks'] = '<b>WICHTIG:</b> Bitte aktualisiere die Nachrichten gleich als Erstes! Du kannst dies bei Adminbereich -> Wartung -> Verschiedenes tun. Wirf bitte vorher noch ein Blick auf die Einstellungen (insbesondere Einstellungen -&gt; Formatierung). Danach solltest Du in Adminbereich -&gt; Wartung -&gt; Nachrichten korrigieren ggf. fehlerhafte Nachrichten berichtigen.';

$LANG['table_exists_error'] = 'Wenn Du eine Neuinstallation durchf&uuml;hren willst, darf keine Tabelle bereits vorhanden sein.<br />Falls Du das Board ein weiteres Mal in dieser Datenbank installieren willst oder aus anderen Gr&uuml;nden die selbe Datenbank nutzen m&ouml;chtest, kannst Du oben auf dieser Seite das "Tabellen-Pr&auml;fix" ver&auml;ndern.';
$LANG['toboard'] = 'Gehe zu Boardsolution';
$LANG['installation_complete'] = 'Die Installation wurde erfolgreich abgeschlossen. Bitte l&ouml;sche die Datei "install.php" jetzt.';
$LANG['writing_install_config_failed'] = 'Die Datei "install/config.php" konnte nicht ver&auml;ndert werden. &Uuml;berpr&uuml;fe bitte ob der CHMOD der Datei 0666 ist.';
$LANG['writing_install_community_failed'] = 'Die Datei "install/community.php" konnte nicht ver&auml;ndert werden. &Uuml;berpr&uuml;fe bitte ob der CHMOD der Datei 0666 ist.';
$LANG['writing_install_mysql_config_failed'] = 'Die Datei "install/mysql_config.php" konnte nicht erstellt werden. &Uuml;berpr&uuml;fe bitte ob der CHMOD des Verzeichnisses "install" 0777 ist.';
?>