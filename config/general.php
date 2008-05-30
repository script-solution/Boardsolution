<?php
/**
 * This file contains some general adjustments for boardsolution and some general constants
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	config
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

// reduce error-reporting-level if debugging is not fully enabled
if(BS_DEBUG < 2)
	error_reporting(E_ALL & ~E_NOTICE);

// The ids of the predefined user-groups
/**
 * The ID of the admin-usergroup
 */
define('BS_STATUS_ADMIN',											1);
/**
 * The ID of the user-usergroup
 */
define('BS_STATUS_USER',											2);
/**
 * The ID of the guest-usergroup
 */
define('BS_STATUS_GUEST',											3);


// The display-locations of the additional-fields
/**
 * A display-location of the additional-fields: the userdetails
 */
define('BS_UF_LOC_USER_DETAILS',							1);
/**
 * A display-location of the additional-fields: the posts
 */
define('BS_UF_LOC_POSTS',											2);
/**
 * A display-location of the additional-fields: the registration (the user can enter the value)
 */
define('BS_UF_LOC_REGISTRATION',							4);
/**
 * A display-location of the additional-fields: the profile (makes the field editable)
 */
define('BS_UF_LOC_USER_PROFILE',							8);


// The login-error codes
/**
 * No error in the login-procedure
 */
define('BS_LOGIN_ERROR_NO_ERROR',							-1);
/**
 * The username or password is empty
 */
define('BS_LOGIN_ERROR_USER_OR_PW_EMPTY',			0);
/**
 * The username is incorrect
 */
define('BS_LOGIN_ERROR_USER_NAME_INCORRECT',	1);
/**
 * The password is incorrect
 */
define('BS_LOGIN_ERROR_PW_INCORRECT',					2);
/**
 * The user is not yet activated
 */
define('BS_LOGIN_ERROR_NOT_ACTIVATED',				3);
/**
 * The user is banned
 */
define('BS_LOGIN_ERROR_BANNED',								4);
/**
 * An admin is required but has not been entered
 */
define('BS_LOGIN_ERROR_ADMIN_REQUIRED',				5);
/**
 * The ip is invalid (empty for example)
 */
define('BS_LOGIN_ERROR_INVALID_IP',						6);
/**
 * A bot has tried to login
 */
define('BS_LOGIN_ERROR_BOT',									7);
/**
 * The max login tries for a user have been reached
 */
define('BS_LOGIN_ERROR_MAX_LOGIN_TRIES',			8);
/**
 * The user has entered an invalid security code (after the max-login-tries had been reached)
 */
define('BS_LOGIN_ERROR_INVALID_SEC_CODE',			9);


// The topic-lock-options
/**
 * Prevents that the topic (name, icon, ...) can be edited
 */
define('BS_LOCK_TOPIC_EDIT',									1);
/**
 * Prevents that the topic can be opened / closed
 */
define('BS_LOCK_TOPIC_OPENCLOSE',							2);
/**
 * Prevents that any post in the topic can be edited
 */
define('BS_LOCK_TOPIC_POSTS',									4);


// The different tasks a user can perform. These are used to determine if the user has the
// permission to perform them it depends on the user-group (and its rights),
// moderator-status and in some cases on the starter of the topic / post.
/**
 * Start a topic
 */
define('BS_MODE_START_TOPIC',									1);
/**
 * Start a poll
 */
define('BS_MODE_START_POLL',									2);
/**
 * Start an event
 */
define('BS_MODE_START_EVENT',									3);
/**
 * Reply to a topic
 */
define('BS_MODE_REPLY',												4);
/**
 * Edit a topic
 */
define('BS_MODE_EDIT_TOPIC',									5);
/**
 * Delete topics
 */
define('BS_MODE_DELETE_TOPICS',								6);
/**
 * Move topics to another forum
 */
define('BS_MODE_MOVE_TOPICS',									7);
/**
 * Edit a post
 */
define('BS_MODE_EDIT_POST',										8);
/**
 * Delete posts
 */
define('BS_MODE_DELETE_POSTS',								9);
/**
 * Split posts to another existing topic or a new one
 */
define('BS_MODE_SPLIT_POSTS',									10);
/**
 * Edit own topics
 */
define('BS_MODE_EDIT_OWN_TOPICS',							11);
/**
 * Open / close topics
 */
define('BS_MODE_OPENCLOSE_TOPICS',						12);
/**
 * Lock topics
 */
define('BS_MODE_LOCK_TOPICS',									13);
/**
 * Mark topics important
 */
define('BS_MODE_MARK_TOPICS_IMPORTANT',				14);


// some general constants
/**
 * The version of boardsolution (please do not change!)
 */
define('BS_VERSION_ID',												'140a1');
define('BS_VERSION',													'Boardsolution v1.40 Alpha1');
#=========================================
?>