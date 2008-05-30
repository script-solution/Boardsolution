<?php
/**
 * This file contains the definitions to export the community.
 * 
 * @version			$Id: community.php 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	config
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

#=========================================
#======= Boardsolution Community =========
#=========================================

/**
 * Set this to true if you export the community
 */
define('BS_ENABLE_EXPORT',false);

/**
 * Function which should return the password which is stored in the database
 * based on the password entered by the user. 
 * 
 * @param string $password the password
 * @param array $data an array with all the fields from the user-table, so that you can
 * 										use the BS_EXPORT_ADD_LOGIN_FIELDS
 * @return string the password to compare with the one stored in the database
 */
function BS_Ex_get_stored_password($password,$data)
{
	return md5($password);
}

#============ MySQL settings =============

/**
 * The user-table in which the following fields are
 * If the table is in another database just put the name of the database in front
 * followed by a dot.
 */
define('BS_TB_USER','ssf_user');
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

/**
 * If you have other fields in the user-table you need to add, you can do this here.
 * For example if you have a "salt-field" for the password or something like this.
 * Just add the fieldnames to the constant:
 * <code>
 *   define('BS_EXPORT_ADD_LOGIN_FIELDS','<field_name_1>,<field_name_2>,...,<field_name_n>');
 * </code>
 * Examples:
 * <code>
 * 	define('BS_EXPORT_ADD_LOGIN_FIELDS','yourfield');
 * 	define('BS_EXPORT_ADD_LOGIN_FIELDS','firstfield,secondfield');
 * </code>
 *
 * You can use this fields for example in the password-function.
 */
define('BS_EXPORT_ADD_LOGIN_FIELDS','');

#============ Board settings =============

/**
 * Disable the registration-link or replace with your own.
 * Valid values are: 'disabled' and 'link'
 */
define('BS_EXPORT_REGISTER_TYPE','disabled');

/**
 * Your own registration-link
 */
define('BS_EXPORT_REGISTER_LINK','');

/**
 * Disable the send-password-link or replace it with your own.
 * Valid values are: 'enabled', 'disabled' and 'link'
 * 
 * 'enabled' could make sense if the community you export to does not have this feature.
 * Note that this 
 */
define('BS_EXPORT_SEND_PW_TYPE','disabled');

/**
 * Your own send-password-link
 */
define('BS_EXPORT_SEND_PW_LINK','');

/**
 * Disable the resend-activation-link or replace it with your own.
 * Valid values are: 'disabled' and 'link'
 */
define('BS_EXPORT_RESEND_ACT_TYPE','disabled');

/**
 * Your own resend-activation-link
 */
define('BS_EXPORT_RESEND_ACT_LINK','');
?>