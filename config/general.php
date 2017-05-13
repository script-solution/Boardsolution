<?php
/**
 * This file contains some general adjustments for boardsolution and some general constants
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

// reduce error-reporting-level if debugging is not fully enabled
if(BS_DEBUG < 2)
{
	// disable deprecated notices when using PHP >= 5.3
	if(version_compare(PHP_VERSION,'5.3.0') >= 0)
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
	else
		error_reporting(E_ALL & ~E_NOTICE);
}

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

// the constants for the old community-export concept
/**
 * The user-id field
 */
define('BS_EXPORT_USER_ID','id');

/**
 * The user-name field
 */
define('BS_EXPORT_USER_NAME','user_name');

/**
 * The password field
 */
define('BS_EXPORT_USER_PW','user_pw');

/**
 * The email field
 */
define('BS_EXPORT_USER_EMAIL','user_email');


// some general constants
/**
 * The version of boardsolution (please do not change!)
 */
define('BS_VERSION_ID',												'146');
define('BS_VERSION',													'Boardsolution v1.46');
#=========================================
?>
