<?php
/**
 * Contains the possible actions of the boardsolution-frontend and the definition after
 * which action should be displayed a status-page
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	config
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

#============= Action-Messages =============
# Hier koennen Sie einstellen bei welcher Aktion eine "Status-Seite"
# angezeigt werden soll und bei welcher nicht.
# Bitte veraendern Sie nicht die Zeilen mit "define(..." sondern
# den Abschnitt weiter unten beginnend mit "$action_msgs = ..."
# Wenn Sie z.B. keine "Status-Seite" nach einem neuen Beitrag anzeigen
# moechten, setzen Sie "BS_ACTION_REPLY" auf "false".
# Here you can configure after which action a "status-page" will
# be displayed. Please don't modify the lines with "define(..." but
# change the part below with "$action_msgs = ..."
# For example if you don't want to display a "status-page" after a
# post you can simple set the value of "BS_ACTION_REPLY" to "false".

/**
 * Reply to a topic
 */
define('BS_ACTION_REPLY',								0);
/**
 * Delete posts of a topic
 */
define('BS_ACTION_DELETE_POSTS',				1);
/**
 * Split posts from a topic to a new one
 */
define('BS_ACTION_SPLIT_POSTS',					2);
/**
 * Edit a post
 */
define('BS_ACTION_EDIT_POST',						3);
/**
 * Start a new topic
 */
define('BS_ACTION_START_TOPIC',					4);
/**
 * Start a new poll
 */
define('BS_ACTION_START_POLL',					5);
/**
 * Start a new event
 */
define('BS_ACTION_START_EVENT',					6);
/**
 * Edit a topic
 */
define('BS_ACTION_EDIT_TOPIC',					7);
/**
 * Edit a poll
 */
define('BS_ACTION_EDIT_POLL',						8);
/**
 * Edit an event
 */
define('BS_ACTION_EDIT_EVENT',					9);
/**
 * Move topics to another forum
 */
define('BS_ACTION_MOVE_TOPICS',					10);
/**
 * Open some topics
 */
define('BS_ACTION_OPEN_TOPICS',					11);
/**
 * Close some topics
 */
define('BS_ACTION_CLOSE_TOPICS',				12);
/**
 * Delete topics
 */
define('BS_ACTION_DELETE_TOPICS',				13);
/**
 * Change the read-status of topics. That means mark topics read/unread or mark forums read
 */
define('BS_ACTION_CHANGE_READ_STATUS',	14);
/**
 * Send a link to change the password by email
 */
define('BS_ACTION_SEND_PW',							15);
/**
 * Change the password (with the link the user got via email)
 */
define('BS_ACTION_CHANGE_PASSWORD',			16);
/**
 * Register
 */
define('BS_ACTION_REGISTER',						17);
/**
 * Vote for a poll
 */
define('BS_ACTION_VOTE',								18);
/**
 * Join an event (in a topic)
 */
define('BS_ACTION_JOIN_EVENT',					19);
/**
 * Leave an event (in a topic)
 */
define('BS_ACTION_LEAVE_EVENT',					20);
/**
 * Send an email to an user
 */
define('BS_ACTION_SEND_EMAIL',					21);
/**
 * Add a link to the linklist
 */
define('BS_ACTION_ADD_LINK',						22);
/**
 * Vote for a link in the linklist
 */
define('BS_ACTION_VOTE_LINK',						23);
/**
 * Edit the personal profile
 */
define('BS_ACTION_EDIT_PERS_PROFILE',		24);
/**
 * Edit the settings in the profile
 */
define('BS_ACTION_EDIT_PERS_CONFIG',		25);
/**
 * Change the password / username in the profile
 */
define('BS_ACTION_CHANGE_USER_PW',			26);
/**
 * Subscribe to a topic
 */
define('BS_ACTION_SUBSCRIBE_TOPIC',			27);
/**
 * Remove a subscription to a topic
 */
define('BS_ACTION_UNSUBSCRIBE_TOPIC',		28);
/**
 * Subscribe to a forum
 */
define('BS_ACTION_SUBSCRIBE_FORUM',			29);
/**
 * Remove a subscription to a forum
 */
define('BS_ACTION_UNSUBSCRIBE_FORUM',		30);
/**
 * Use an avatar
 */
define('BS_ACTION_USE_AVATAR',					31);
/**
 * Remove an avatar (not delete it)
 */
define('BS_ACTION_REMOVE_AVATAR',				32);
/**
 * Delete an avatar
 */
define('BS_ACTION_DELETE_AVATAR',				33);
/**
 * Upload an avatar
 */
define('BS_ACTION_UPLOAD_AVATAR',				34);
/**
 * Remove an user from the banlist
 */
define('BS_ACTION_UNBAN_USER',					35);
/**
 * Add an user to the banlist
 */
define('BS_ACTION_BAN_USER',						36);
/**
 * Send a private message
 */
define('BS_ACTION_SEND_PM',							37);
/**
 * Delete private messages
 */
define('BS_ACTION_DELETE_PMS',					38);
/**
 * Mark some PMs read
 */
define('BS_ACTION_MARK_PMS_READ',				39);
/**
 * Mark some PMs unread
 */
define('BS_ACTION_MARK_PMS_UNREAD',			40);
/**
 * Add an event to the calendar
 */
define('BS_ACTION_CAL_ADD_EVENT',				41);
/**
 * Edit an event in the calendar
 */
define('BS_ACTION_CAL_EDIT_EVENT',			42);
/**
 * Delete an event in the calendar
 */
define('BS_ACTION_CAL_DEL_EVENT',				43);
/**
 * Join an event in the calendar
 */
define('BS_ACTION_CAL_JOIN_EVENT',			44);
/**
 * Leave an event in the calendar
 */
define('BS_ACTION_CAL_LEAVE_EVENT',			45);
/**
 * Subscribe to all forums
 */
define('BS_ACTION_SUBSCRIBE_ALL',				46);
/**
 * Resend the activation email
 */
define('BS_ACTION_RESEND_ACT_LINK',			47);
/**
 * Login to the board
 */
define('BS_ACTION_LOGIN',								48);
/**
 * Logout from the board
 */
define('BS_ACTION_LOGOUT',							49);
/**
 * Move posts from one topic to another (existing) one.
 */
define('BS_ACTION_MERGE_POSTS',					50);
/**
 * Lock topics (so that just admins and mods can perform the specified actions)
 */
define('BS_ACTION_LOCK_TOPICS',					51);
/**
 * Edit the signature in the profile
 */
define('BS_ACTION_EDIT_SIGNATURE',			52);
/**
 * Save the favorite forums
 */
define('BS_ACTION_SAVE_FAVFORUMS',			53);

# Hier bitte ggf. die Aenderungen durchfuehren!
# Please perform the changes here!
$action_msgs = array(
	BS_ACTION_REPLY =>										false,
	BS_ACTION_DELETE_POSTS =>							true,
	BS_ACTION_SPLIT_POSTS =>							true,
	BS_ACTION_EDIT_POST =>								true,
	BS_ACTION_START_TOPIC =>							true,
	BS_ACTION_START_POLL =>								true,
	BS_ACTION_START_EVENT =>							true,
	BS_ACTION_EDIT_TOPIC =>								true,
	BS_ACTION_EDIT_POLL =>								true,
	BS_ACTION_EDIT_EVENT => 							true,
	BS_ACTION_MOVE_TOPICS =>							true,
	BS_ACTION_OPEN_TOPICS =>							true,
	BS_ACTION_CLOSE_TOPICS =>							true,
	BS_ACTION_DELETE_TOPICS =>						true,
	BS_ACTION_CHANGE_READ_STATUS =>				false,
	BS_ACTION_SEND_PW =>									true,
	BS_ACTION_CHANGE_PASSWORD =>					true,
	BS_ACTION_REGISTER =>									true,
	BS_ACTION_VOTE =>											true,
	BS_ACTION_JOIN_EVENT =>								true,
	BS_ACTION_LEAVE_EVENT =>							true,
	BS_ACTION_SEND_EMAIL =>								true,
	BS_ACTION_ADD_LINK =>									true,
	BS_ACTION_VOTE_LINK =>								true,
	BS_ACTION_EDIT_PERS_PROFILE =>				true,
	BS_ACTION_EDIT_PERS_CONFIG =>					true,
	BS_ACTION_CHANGE_USER_PW =>						true,
	BS_ACTION_SUBSCRIBE_TOPIC =>					true,
	BS_ACTION_UNSUBSCRIBE_TOPIC =>				true,
	BS_ACTION_SUBSCRIBE_FORUM => 					true,
	BS_ACTION_UNSUBSCRIBE_FORUM => 				true,
	BS_ACTION_USE_AVATAR =>								false,
	BS_ACTION_REMOVE_AVATAR =>						false,
	BS_ACTION_DELETE_AVATAR =>						true,
	BS_ACTION_UPLOAD_AVATAR =>						true,
	BS_ACTION_UNBAN_USER =>								true,
	BS_ACTION_BAN_USER =>									true,
	BS_ACTION_SEND_PM =>									true,
	BS_ACTION_DELETE_PMS =>								true,
	BS_ACTION_MARK_PMS_READ =>						false,
	BS_ACTION_MARK_PMS_UNREAD =>					false,
	BS_ACTION_CAL_ADD_EVENT =>						true,
	BS_ACTION_CAL_EDIT_EVENT =>						true,
	BS_ACTION_CAL_DEL_EVENT =>						true,
	BS_ACTION_CAL_JOIN_EVENT =>						true,
	BS_ACTION_CAL_LEAVE_EVENT =>					true,
	BS_ACTION_SUBSCRIBE_ALL =>						true,
	BS_ACTION_RESEND_ACT_LINK =>					true,
	BS_ACTION_LOGIN =>										false,
	BS_ACTION_LOGOUT =>										false,
	BS_ACTION_MERGE_POSTS =>							true,
	BS_ACTION_LOCK_TOPICS =>							true,
	BS_ACTION_EDIT_SIGNATURE =>						true,
	BS_ACTION_SAVE_FAVFORUMS =>						true
);


# The actions for the ACP
define('BS_ACP_ACTION_DELETE_ATTACHMENTS',					1);
define('BS_ACP_ACTION_DELETE_AVATARS',							2);
define('BS_ACP_ACTION_IMPORT_AVATARS',							3);
define('BS_ACP_ACTION_DELETE_BANS',									4);
define('BS_ACP_ACTION_ADD_BAN',											5);
define('BS_ACP_ACTION_UPDATE_BANS',									6);
define('BS_ACP_ACTION_ADD_BOT',											7);
define('BS_ACP_ACTION_DELETE_BOTS',									8);
define('BS_ACP_ACTION_EDIT_BOT',										9);
define('BS_ACP_ACTION_ADD_LANGUAGE',								10);
define('BS_ACP_ACTION_DELETE_LANGUAGES',						11);
define('BS_ACP_ACTION_UPDATE_LANGUAGES',						12);
define('BS_ACP_ACTION_DELETE_LINKS',								13);
define('BS_ACP_ACTION_ACTIVATE_LINKS',							14);
define('BS_ACP_ACTION_DEACTIVATE_LINKS',						15);
define('BS_ACP_ACTION_EDIT_LINK',										16);
define('BS_ACP_ACTION_SWITCH_SMILEYS',							17);
define('BS_ACP_ACTION_DELETE_SMILEYS',							18);
define('BS_ACP_ACTION_IMPORT_SMILEYS',							19);
define('BS_ACP_ACTION_EDIT_SMILEY',									20);
define('BS_ACP_ACTION_DELETE_SUBSCRIPTIONS',				21);
define('BS_ACP_ACTION_REGENERATE_CACHE',						22);
define('BS_ACP_ACTION_DELETE_IPLOGS',								23);
define('BS_ACP_ACTION_DELETE_ALL_IPLOGS',						24);
define('BS_ACP_ACTION_DELETE_ERRORLOGS',						25);
define('BS_ACP_ACTION_DELETE_ALL_ERRORLOGS',				26);
define('BS_ACP_ACTION_SEND_ERRORS',									27);
define('BS_ACP_ACTION_EDIT_TASK',										28);
define('BS_ACP_ACTION_RUN_TASK',										29);
define('BS_ACP_ACTION_ADD_TASK',										30);
define('BS_ACP_ACTION_DELETE_TASKS',								31);
define('BS_ACP_ACTION_UPDATE_USERRANKS',						32);
define('BS_ACP_ACTION_DELETE_USERRANKS',						33);
define('BS_ACP_ACTION_ADD_USERRANK',								34);
define('BS_ACP_ACTION_EDIT_ADDFIELD',								35);
define('BS_ACP_ACTION_ADD_ADDFIELD',								36);
define('BS_ACP_ACTION_DELETE_ADDFIELDS',						37);
define('BS_ACP_ACTION_EDIT_TPL',										39);
define('BS_ACP_ACTION_UPDATE_FORUMS',								40);
define('BS_ACP_ACTION_ADD_FORUM',										41);
define('BS_ACP_ACTION_EDIT_FORUM',									42);
define('BS_ACP_ACTION_DELETE_FORUMS',								43);
define('BS_ACP_ACTION_TRUNCATE_FORUMS',							44);
define('BS_ACP_ACTION_SWITCH_FORUMS',								45);
define('BS_ACP_ACTION_RESORT_FORUMS',								46);
define('BS_ACP_ACTION_UPDATE_THEMES',								47);
define('BS_ACP_ACTION_ADD_THEME',										48);
define('BS_ACP_ACTION_DELETE_THEMES',								49);
define('BS_ACP_ACTION_ADD_MODERATORS',							50);
define('BS_ACP_ACTION_REMOVE_MODERATORS',						51);
define('BS_ACP_ACTION_ADD_USER_GROUP',							52);
define('BS_ACP_ACTION_EDIT_USER_GROUP',							53);
define('BS_ACP_ACTION_DELETE_USER_GROUPS',					54);
define('BS_ACP_ACTION_SAVE_SETTINGS',								55);
define('BS_ACP_ACTION_REVERT_SETTING',							66);
define('BS_ACP_ACTION_THEME_EDITOR_SIMPLE_SAVE',		67);
define('BS_ACP_ACTION_THEME_EDITOR_SIMPLE_DELETE',	68);
define('BS_ACP_ACTION_THEME_EDITOR_SIMPLE_ADD',			69);
define('BS_ACP_ACTION_THEME_EDITOR_ADVANCED_SAVE',	70);
define('BS_ACP_ACTION_USER_EDIT_UGROUPS',						71);
define('BS_ACP_ACTION_USER_EDIT',										72);
define('BS_ACP_ACTION_USER_DELETE',									73);
define('BS_ACP_ACTION_USER_BAN',										74);
define('BS_ACP_ACTION_USER_UNBAN',									75);
define('BS_ACP_ACTION_USER_ADD',										76);
define('BS_ACP_ACTION_USER_ACT_ACTIVATE',						77);
define('BS_ACP_ACTION_USER_ACT_DELETE',							78);
define('BS_ACP_ACTION_ACPACCESS_MODULE',						79);
define('BS_ACP_ACTION_ACPACCESS_USER',							80);
define('BS_ACP_ACTION_ACPACCESS_GROUP',							81);
define('BS_ACP_ACTION_SWITCH_ADDFIELDS',						82);
define('BS_ACP_ACTION_CONFIG_MOD_FORUMS',						83);
define('BS_ACP_ACTION_ADD_BBCODE',									84);
define('BS_ACP_ACTION_EDIT_BBCODE',									85);
define('BS_ACP_ACTION_DELETE_BBCODES',							86);
define('BS_ACP_ACTION_RESORT_SMILEYS',							87);

# The actions for the dbbackup-script
define('BS_DBA_ACTION_DELETE_BACKUPS',							1);
define('BS_DBA_ACTION_DELETE_TABLES',								2);
define('BS_DBA_ACTION_OPTIMIZE_TABLES',							3);
define('BS_DBA_ACTION_IMPORT_BACKUP',								4);
?>