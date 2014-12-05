<?php
/**
 * Some additional settings which may be interesting. These settings are designed to be
 * 
 * @package			Boardsolution
 * @subpackage	config
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

############################ IMPORTANT ############################

/**
 * Der Zeichensatz, der verwendet werden soll, um Daten aus der Datenbank zu lesen. Dieser sollte
 * dem Zeichensatz in der DB entsprechen und auch dem im HTML-Dokument.
 * Siehe BS_HTML_CHARSET.
 * Beachten Sie, dass dies nur einen Effekt bei MySQL 4.1 und neuer hat!
 * Sie koennen verfuegbare Zeichensaetze unter anderem mit dem SQL-Befehl "SHOW CHARACTER SET;"
 * ermitteln.
 *
 * The character-set to use for reading data from the database. This charset should be equal
 * to the one in the DB and also to the one in the HTML-document.
 * See BS_HTML_CHARSET.
 * Note that this has just an effect with MySQL 4.1 and later!
 * You can check for available charsets amongst others with the SQl statement "SHOW CHARACTER SET;".
 */
define('BS_DB_CHARSET','utf8');

/**
 * Der Zeichensatz, der im HTML-Dokument angegeben wird, d.h. im Meta-Tag:
 * <code>
 * 	<meta http-equiv="Content-Type" content="text/html; charset=IhrZeichenSatz" />
 * </code>
 * Sie koennen dies auch unabhaengig von der MySQL-Version festlegen:
 * <code>
 * 	define('BS_HTML_CHARSET','ISO-8859-1');
 * </code>
 *
 * The charset which will be used in the HTML-document, that means in the meta-tag:
 * <code>
 * 	<meta http-equiv="Content-Type" content="text/html; charset=YourCharSet" />
 * </code>
 * You don't have to define this depending on the MySQL-version:
 * <code>
 * 	define('BS_HTML_CHARSET','ISO-8859-1');
 * </code>
*/
define('BS_HTML_CHARSET','UTF-8');

/**
 * Die Datei, mit welcher das Board aufgerufen wird. Standardmaessig ist dies immer "index.php".
 * Falls Sie das Board einbinden, ist es ggf. notwendig den Wert zu aendern.
 * Dabei koennen auch Parameter mit angegeben werden. Z.B. wenn Sie das Board mit
 * "seite.php?site=forum" aufrufen, muesste der Wert dementsprechend "seite.php?site=forum" sein.
 * 
 * The file with which the board is called. By default this is always "index.php".
 * If you include the board it may be necessary to change this value.
 * You can also add parameters if this is required. For example if you call the board with
 * "page.php?site=forum" you have to use the value "page.php?site=forum".
 */
define('BS_FRONTEND_FILE','index.php');

############################ GENERAL ############################

/**
 * Der Pfad zu FrameWorkSolution (relativ) mit / am Ende.
 * 
 * The path to FrameWorkSolution (relative) with trailing slash.
 */
define('BS_FWS_PATH','fws/');

/**
 * Hiermit koennen Debug-Infos konfiguriert werden:
 * 	0 = abgeschaltet
 * 	1 = nur Zeit, Query-Anzahl und Memory-Usage anzeigen
 * 	2 = Alle Infos anzeigen
 * 
 * Configures debugging infos:
 * 	0 = disabled
 * 	1 = display just time, query-count and memory-usage
 * 	2 = display all infos
 */
define('BS_DEBUG',0);

/**
 * Legt fest ob der Calltrace bei Fehler angezeigt werden soll, d.h. welche Funktionen aufgerufen
 * wurden inkl. ein Codeausschnitt dieser (sofern es keine sensible Datei ist).
 *
 * Sets wether the calltrace should be displayed if an error occurrs, i.e. which functions have
 * been called including a small code-section (if it's no sensible file).
 */
define('BS_ERRORS_SHOW_CALLTRACE',true);

/**
 * Legt fest ob Fehlermeldungen zusätzlich als BBCode (ausklappbar) angezeigt werden sollen. Dies kann
 * hilfreich sein, wenn man die Meldung in einem Forum o.ae. posten moechte.
 *
 * Sets wether error-messages should be displayed as BBCode (foldout), too. This can be helpful
 * if you want to post this message in a forum or similar.
 */
define('BS_ERRORS_SHOW_BBCODE',true);

/**
 * Falls Sie nicht MyISAM sondern z.B. InnoDB als MySQL-Storage-Engine benutzen, d.h. eine Storage-
 * Engine, die Transaktionen unterstuetzt, koennen (und sollten) Sie diesen Wert auf "true" setzen.
 * Dadurch wird sichergestellt, dass Aenderungen an der Datenbank ganz oder gar nicht durchgefuehrt
 * werden. So bleibt die Datenbank in einem konsistenten Zustand auch wenn ein Query fehlschlaegt,
 * das Script abgebrochen wird oder aehnlich.
 *
 * If you don't use MyISAM but InnoDB as MySQL-storage-engine, that means a storage-engine which
 * supports transactions, you can (and should) set this value to "true". This ensures that changes
 * to the database will be done completely or not at all. That means the database remains in a
 * consistent status even if a query fails, the script has been terminated or similar.
 */
define('BS_USE_TRANSACTIONS',false);

/**
 * Das Praefix der vom Board gesetzten Cookies
 * 
 * The prefix of the cookies which are set by the board
 */
define('BS_COOKIE_PREFIX','bs_');

/**
 * Die Dauer fuer welche die Cookies gespeichert werden (in Sekunden)
 * 
 * The lifetime of the cookies (in seconds)
 */
define('BS_COOKIE_LIFETIME',86400 * 365);

/**
 * Wenn ein User nach dieser Zeit keinen Klick im Board gemacht hat,
 * gilt dieser als offline (in Sekunden)
 * 
 * After this time a user will be treaten as offline if he isn't
 * active anymore (in seconds)
 */
define('BS_ONLINE_TIMEOUT',300);

/**
 * Wenn ein User im Adminbereich nach dieser Zeit keinen Klick im Board gemacht hat,
 * gilt dieser als offline (in Sekunden).
 * Beachten Sie, dass im Adminbereich kein automatisches Einloggen via Cookie stattfindet! D.h.
 * man muss sich nach Ablauf dieser Zeit erneut einloggen.
 * 
 * After this time a user in the adminarea will be treaten as offline if he isn't
 * active anymore (in seconds).
 * Note that you will not be logged in automatically in the adminarea via cookie! That means that
 * you have to login again after this time has elapsed.
 */
define('BS_ACP_ONLINE_TIMEOUT',1800);

############################ ADMINAREA ############################

/**
 * Die Anzahl der Emails, welche bei der Mass-Email-Funktion pro
 * Seite verschickt werden, falls nicht BCC als Mail-Methode benutzt wird.
 * 
 * The number of emails which will be sent per page by the
 * mass-email-function if you're not using the BCC-method to send the mails
 */
define('BS_EMAILS_PER_PAGE',50);

/**
 * Die Anzahl der Operationen pro Seite bei Adminbereich -> Wartung -> Verschiedenes.
 * Wenn ein Aufruf der Aktionen nicht beendet wird, koennte der Server zu langsam sein.
 * In diesem Fall sollte hier der Wert verringert werden.
 * 
 * The number of operations per page in the adminarea -> maintenace -> miscellaneous.
 * If a request of the actions doesn't finish the server might be too slow.
 * In this case you should decrease the value here.
 */
define('BS_MM_OPERATIONS_PER_CYCLE',200);

/**
 * Hier koennen Sie GZip im Adminbereich aktivieren. Es unterstuetzt nicht jeder Server,
 * aber sofern Ihr Server es tut, sollten Sie es aktivieren, da es besonders bei langsamen
 * Internetverbindungen zu einer enormen Geschwindigkeitsverbesserung faehrt.
 * 
 * Here you can enable GZip in the adminarea. Not every server supports it but if your
 * server does you should enable it because it will improve the speed enormously especially with
 * slow internet connections.
 */
define('BS_ENABLE_ADMIN_GZIP',false);

/**
 * Der Username der beim Loeschen von Usern verwendet wird um die Themen und Beitraege zu
 * anomymisieren.
 * 
 * The username that is used when deleting users to make the topics and posts anonymous.
 */
define('BS_ANONYMOUS_NAME','Anonymous');

############################ FORUMS ############################

/**
 * Falls nicht alle Unterforen direkt angezeigt werden, koennen Sie
 * hier die Anzahl an Unterforen-Links, die klein angezeigt werden,
 * anpassen.
 * 
 * If you don't display all subforums directly you can adjust here
 * the number of subforum-links which be displayed very small in the
 * corresponding parent-forum.
 */
define('BS_FORUM_SMALL_SUBDIR_DISPLAY',3);

/**
 * Die maximale Laenge von Forum-Namen, die angezeigt wird. Sobald ein
 * Forum-Name laenger ist, wird dieser verkuerzt.
 * 
 * The maximum length of forum-names which will be displayed. If a
 * forum-name is longer it will be shorten.
 */
define('BS_MAX_FORUM_TITLE_LENGTH',30);

/**
 * Die maximale Anzahl an Moderatoren, die bei einem Forum angezeigt werden.
 * Falls es mehr gibt, wird am Ende ein Link zur Team-Seite angezeigt.
 * 
 * The maximum number of moderators which will be displayed at a forum.
 * If there are more the board will display a link to the team-page at the end.
 */
define('BS_MAX_MODS_DISPLAY',6);

############################ TOPICS ############################

/**
 * Die maximale Laenge von Themen-Namen, die bei dem Feld "Letzter Beitrag" angezeigt wird.
 * 
 * The maximum length of the topic-names which will be displayed at the field "last post".
 */
define('BS_MAX_TOPIC_LENGTH_LAST_POST',13);

/**
 * Die Anzahl der Beitraege, die z.B. beim editieren von Beitraegen angezeigt
 * werden (in umgekehrter Reihenfolge)
 * 
 * The number of posts which will be displayed for example if you edit a
 * post (in reverse order)
 */
define('BS_TOPIC_REVIEW_POST_COUNT',20);

/**
 * Legt die Anzahl der Themen-Icons fest. Die Icons befinden sich in:
 * <code>themes/<theme>/images/thread_type/</code>
 * und sind nach folgendem Schema benannt:
 * <code>symbol_<number>.gif</code>
 * Falls Sie neue Icons hinzufuegen moechten, brauchen Sie nur weitere Icons in das
 * genannte Verzeichnis laden, diese nach dem gleichen Schema benennen und hier die
 * Anzahl der Icons anpassen.
 * 
 * Defines the number of topic-icons. The icons are stored in:
 * <code>themes/<theme>/images/thread_type/</code>
 * and are named by the following scheme:
 * <code>symbol_<number>.gif</code>
 * If you would like to add more icons you just have to load additional icons into the
 * mentioned folder, name them like the same scheme and adjust the number of icons
 * here.
 */
define('BS_NUMBER_OF_TOPIC_ICONS',8);

############################ USER-PROFILE ############################

/**
 * Hier koennen Sie einstellen wieviele Erfahrungspunkte ein User fuer ein Beitrag
 * bzw. fuer das Erstellen eines Themas bekommt
 * WICHTIG: Wenn Sie diesen Wert aendern wollen und schon Beitraege/Themen vorhanden sind,
 * muessen Sie hinterher im Adminbereich bei "Wartung" -> "Verschiedenes" die Aktion
 * "User Beitraege / Erfahrung" ausfuehren!
 * 
 * Here you can configure how many experiencepoints a user receives for a post
 * respectivly the creation of a topic
 * IMPORTANT: If you want to change this value and posts/topics are already existing
 * you have to execute the action "User posts / experience" in the adminarea in
 * the section "Maintenance" -> "Miscellaneous" afterwards!
 */
define('BS_EXPERIENCE_FOR_POST',1);
define('BS_EXPERIENCE_FOR_TOPIC',2);

/**
 * Die Anzahl an Avataren, die im Profil pro Seite angezeigt wird.
 * 
 * The number of avatars displayed per page in the profile.
 */
define('BS_AVATARS_PER_PAGE',8);

/**
 * Die maximale Anzahl an Ungelesenen Themen, die gespeichert werden fuer jeden User.
 * 
 * The maximum number of unread topics that will be saved (for each user).
 */
define('BS_MAX_UNREAD_TOPICS',400);

/**
 * Die Anzahl an abonnierten Themen, die im Profil pro Seite angezeigt wird.
 * 
 * The number of subscribed topics per page in the profile.
 */
define('BS_SUBSCR_TOPICS_PER_PAGE',15);

/**
 * Die Anzahl an abonnierten Foren, die im Profil pro Seite angezeigt wird.
 * 
 * The number of subscribed forums per page in the profile.
 */
define('BS_SUBSCR_FORUMS_PER_PAGE',15);

/**
 * Die Anzahl der PMs, die beim Antworten auf PMs angezeigt werden
 * 
 * The number of PMs which will be displayed if you answer a PM 
 */
define('BS_PM_REVIEW_MESSAGE_COUNT',10);

/**
 * Die Anzahl an PMs, die auf der Übersichtsseite pro Seite angezeigt wird.
 * 
 * The number of PMs per page in the PM-overview
 */
define('BS_PMS_OVERVIEW_PER_PAGE',5);

/**
 * Die Anzahl an PMs, die in der In-/Outbox pro Seite angezeigt wird.
 * 
 * The number of PMs per page in the in-/outbox
 */
define('BS_PMS_FOLDER_PER_PAGE',15);

/**
 * Die maximale Laenge von PM-Titeln, die angezeigt wird. Sobald ein
 * PM-Titel laenger ist, wird dieser verkuerzt.
 * 
 * The maximum length of PM-titles which will be displayed. If a
 * PM-title is longer it will be shorten.
 */
define('BS_MAX_PM_TITLE_LEN',45);

/**
 * Die maximale Anzahl an Empfaengern pro PM
 * 
 * The maximum number of receivers per PM
 */
define('BS_MAX_PM_RECEIVER',5);

/**
 * Hier koennen Sie die Ueberpruefung der maximalen 'RE: ' Angaben je PM aktivieren.
 *
 * Here you could enable the check of the maximum 'RE: ' statements in the title of a PM
 */
define('BS_ENABLE_RE_STMT_CHECK', true);

/**
 * Die maximale Anzahl an 'RE: ' Angaben im Titel einer PM
 *
 * The maximum number of 'RE: ' statements in the title of a PM
 */
define('BS_MAX_RE_STMT', 3);

/**
 * Ab wieviel Prozent der erlaubten Emails in der PM-Inbox soll dem User bei weiteren PMs
 * eine Email geschickt werden? D.h. wenn der Wert auf 90 steht und die max. PMs in der Inbox
 * auf 100 gestellt sind, wird dem User ab 90 PMs in der Inbox bei jeder weiteren PM eine Email
 * geschickt, die darauf aufmerksam macht, dass PMs gel&ouml;scht werden sollten, damit weitere
 * empfangen werden koennen.
 *
 * The minimum percentage of allowed PMs in the inbox to send the user an email. That means if
 * the value is set to 90 and the max. PMs in the inbox is set to 100 the user will get an email
 * as soon as she/he has at least 90 PMs in the inbox. For every PM she/he will get an email
 * that contains a notice that the user should delete some PMs to receive new ones.
 */
define('BS_PM_INBOX_FULL_EMAIL_SINCE',90);

/**
 * Die Anzahl der Themen bei "Letzte Aktivitaet" in den Userdetails
 * 
 * The number of topics at "Last activity" in the userdetails
 */
define('BS_USER_DETAILS_TOPIC_COUNT',5);

############################ BBCODE ############################

/**
 * Das maximale level von verschachtelten BBCode-Tags.
 * 
 * The maximum level of nested bbcode-tags
 */
define('BS_BBCODE_MAX_NESTED_LEVEL',5);

/**
 * Wollen Sie eingefuegte Smileys automatisch mit Leerzeichen umranden?
 * 
 * Do you want to surround inserted smileys automaticly with spaces?
 */
define('BS_SPACES_AROUND_SMILEYS',true);

/**
 * Syntax-Highlighting ist sehr zeitaufwendig. Daher muss es auf irgendeine Art und Weise
 * begrenzt werden. Boardsolution tut dies mit dieser Konstante. Es definiert die maximale Anzahl
 * an Zeichen, die "gehighlighted" werden sollen. Dies gilt fuer den gesamten Text einer Nachricht.
 * Zum Beispiel:
 * Angenommen wir haben eine Nachricht mit 3 Code-Bereichen (mit Syntax-Highlighting) mit den
 * folgenden Laengen: 500, 1200, 600. Und das Limit ist 2000.
 * Boardsolution wuerde den ersten und zweiten Bereich highlighten, aber nicht den dritten. Denn
 * dieser wuerde nicht mehr hineinpassen ((500 + 1200 + 600) > 2000).
 * 
 * Highlighting code is very time consuming. Therefore we have to limit it somehow. Boardsolution
 * uses this constant to do so. It defines the maximum number of characters that should be
 * highlighted. This is valid for the complete text of a message.
 * For example:
 * Assume we have a message that has 3 code-areas (with highlighting) with the
 * following length: 500, 1200, 600. And the limit is 2000.
 * Boardsolution would highlight the first and second area but not the third. Because it does not
 * fit anymore ((500 + 1200 + 600) > 2000).
 */
define('BS_CODE_HIGHLIGHT_LIMIT',2500);

############################ MODULES ############################

/**
 * Die Anzahl der Links pro Seite in der Linkliste
 * 
 * The number of links per page in the linklist
 */
define('BS_LINKLIST_LINKS_PER_PAGE',15);

/**
 * Die maximale Anzahl der Termine und Geburtstage, die in der Ministatistik angezeigt werden
 * 
 * The maximum number of events and birthdays which will be displayed in the ministatistics
 */
define('BS_MINISTATS_MAX_EVENTS',5);

/**
 * Die Termine in der Ministatistik. Wie weit in die Zukunft sollen die Termine angezeigt
 * werden (in Tagen)
 * 
 * The events in the ministatistics. How far into the future do you want to display
 * events? (in days)
 */
define('BS_MINISTATS_EVENT_DAYS',5);

/**
 * Die minimale Laenge jedes Suchbegriffes
 * 
 * The minimum length of every search-keyword
 */
define('BS_SEARCH_MIN_KEYWORD_LEN',3);

/**
 * Der Zeichensatz fuer den RSS-Feed. Z.B. ISO-8859-1 oder UTF-8
 *
 * The charset for the RSS-feed. For example ISO-8859-1 or UTF-8
 */
define('BS_RSS_FEED_ENCODING','UTF-8');

############################ TASKS ############################

/**
 * Wieviele zuletzt aktive Foren sollen im Portal (max.) angezeigt werden?
 * 
 * How many last active forums should be displayed (max.) in the portal?
 */
define('BS_PORTAL_LAST_FORUMS_COUNT',5);

/**
 * Nach wievielen Sekunden sollen nicht aktivierte User-Accounts
 * geloescht werden? Dies wird von dem Task "Registrierungen"
 * uebernommen. (Standard = 1 Woche)
 * Dies gilt ebenfalls fuer nicht benutzte Passwort- und Email-Aenderungen
 * 
 * After how many seconds do you want to delete not activated
 * user-accounts? This will be done by the task
 * "registrations". (default = 1 week)
 * This will also be taken for not used password- and email-changes
 */
define('BS_DEAD_REG_DELETE_INTERVAL',7 * 86400);

/**
 * Nach wievielen Sekunden nach dem letzten Beitrag in einem Thema
 * sollen Abonnements dieses Themas wieder geloescht werden? Dies
 * wird von dem Task "Abonnements" uebernommen. (Standard = ~2 Monate)
 * 
 * How many seconds after the last post in a topic do you want to
 * delete subscriptions to this topics? This will be done by the
 * task "Subscriptions". (default = ~2 months)
 */
define('BS_SUBSCRIPTION_TIMEOUT',60 * 86400);

/**
 * Nach wievielen Sekunden soll ein Anhang als "tot" gelten?
 * Dies wird hauptsaechlich verwendet um mit dem "Anhang-Task" tote Anhaenge zu loeschen
 * und dabei kein Anhang zu loeschen, der vor wenigen Augenblicken hochgeladen
 * wurde und das Thema / der Beitrag / die PM noch nicht fertig geschrieben wurde.
 * 
 * After how many seconds should an attachment be treaten as "dead"?
 * This will be used primary for the "attachment-task" to delete dead attachments
 * and don't delete attachments which have just been uploaded and the topic / post / PM
 * has not been finished yet.
 */
define('BS_ATTACHMENT_TIMEOUT',3600);


#============= GET-Parameter =============
# Falls Sie einen dieser Werte schon auf Ihrer Homepage in der
# URL verwenden und das Board per PHP einbinden, koennen Sie die
# Werte hier veraendern um Konflikte zu vermeiden.
#
# If you are already using one of this values in the URL and you
# include Boardsolution with PHP you can edit them here to prevent
# conflicts.

# general
define('BS_URL_ACTION',							'action');
define('BS_URL_SUB',								'sub');
define('BS_URL_AT',									'at');
define('BS_URL_FID',								'fid');
define('BS_URL_TID',								'tid');
define('BS_URL_PID',								'pid');
define('BS_URL_LOC',								'loc');
define('BS_URL_MODE',								'mode');
define('BS_URL_ID',									'id');
define('BS_URL_SITE',								'site');
define('BS_URL_ORDER',							'order');
define('BS_URL_AD',									'ad');
define('BS_URL_LIMIT',							'limit');
define('BS_URL_DEL',								'del');
define('BS_URL_HL',									'hl');
define('BS_URL_KW',									'kw');
define('BS_URL_SEARCH_MODE',						'sm');
define('BS_URL_UN',									'un');
define('BS_URL_SID',								'sid');
define('BS_URL_PAGE',								'page');
define('BS_URL_CURRENT',							'cmod');

# calendar
define('BS_URL_DAY',								'day');
define('BS_URL_WEEK',								'week');
define('BS_URL_MONTH',							'month');
define('BS_URL_YEAR',								'year');

# memberlist
define('BS_URL_MS_NAME',						'msn');
define('BS_URL_MS_EMAIL',						'mse');
define('BS_URL_MS_GROUP',						'msg');
define('BS_URL_MS_FROM_POSTS',			'msfp');
define('BS_URL_MS_TO_POSTS',				'mstp');
define('BS_URL_MS_FROM_POINTS',			'msfpts');
define('BS_URL_MS_TO_POINTS',				'mstpts');
define('BS_URL_MS_FROM_REG',				'msfr');
define('BS_URL_MS_TO_REG',					'mstr');
define('BS_URL_MS_FROM_LASTLOGIN',	'msfl');
define('BS_URL_MS_TO_LASTLOGIN',		'mstl');
define('BS_URL_MS_MODS',						'msm');
?>