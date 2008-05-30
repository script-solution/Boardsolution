<?php
/**
 * Contains the full-object-class
 *
 * @version			$Id: fullobject.php 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * This will be used as the base-class for all classes in the library and this project
 * which require some properties.
 * It is just used to have code-completion (otherwise some IDEs don't know the
 * type of the properties).
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class PLIB_FullObject extends PLIB_Object
{
	/**
	 * The db-connection class
	 *
	 * @var PLIB_MySQL
	 */
	private $db;

	/**
	 * The input-class
	 *
	 * @var PLIB_Input
	 */
	private $input;

	/**
	 * The cookie-handling object
	 *
	 * @var PLIB_Cookies
	 */
	private $cookies;

	/**
	 * The locale-object
	 *
	 * @var BS_Locale
	 */
	private $locale;

	/**
	 * The template-object
	 *
	 * @var PLIB_Template_Handler
	 */
	private $tpl;

	/**
	 * The session-manager-object
	 *
	 * @var BS_Session_Manager
	 */
	private $sessions;
	
	/**
	 * The current user
	 *
	 * @var BS_User_Current
	 */
	private $user;

	/**
	 * The object for the URL-creation
	 *
	 * @var BS_URL
	 */
	private $url;
	
	/**
	 * The document
	 *
	 * @var BS_Document
	 */
	private $doc;
	
	/**
	 * The messages-object
	 *
	 * @var BS_Messages
	 */
	private $msgs;

	/**
	 * Some general functions
	 *
	 * @var BS_Functions
	 */
	private $functions;
	
	/**
	 * The settings
	 *
	 * @var array
	 */
	private $cfg;

	/**
	 * The Auth-object
	 *
	 * @var BS_Auth
	 */
	private $auth;

	/**
	 * The unread-object
	 *
	 * @var BS_Unread
	 */
	private $unread;
	
	/**
	 * The cache-container
	 *
	 * @var BS_Cache_Container
	 */
	private $cache;
	
	/**
	 * The forums-manager
	 *
	 * @var BS_Forums_Manager
	 */
	private $forums;
	
	/**
	 * The ip-helper
	 *
	 * @var BS_IPs
	 */
	private $ips;
}
?>