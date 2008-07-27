<?php
/**
 * Contains the property-accessor-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The property-accessor for Boardsolution. We change and add some properties to the predefined
 * ones.
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_PropAccessor extends PLIB_PropAccessor
{
	/**
	 * @return BS_Page the document
	 */
	public function doc()
	{
		return $this->_doc;
	}
	
	/**
	 * @return BS_Auth the auth-property
	 */
	public function auth()
	{
		return parent::get('auth');
	}
	
	/**
	 * @return BS_IPs the ips-property
	 */
	public function ips()
	{
		return parent::get('ips');
	}
	
	/**
	 * @return BS_Unread the unread-property
	 */
	public function unread()
	{
		return parent::get('unread');
	}
	
	/**
	 * @return BS_Forums_Manager the forums-property
	 */
	public function forums()
	{
		return parent::get('forums');
	}
	
	/**
	 * @return PLIB_Cache_Container the cache-property
	 */
	public function cache()
	{
		return parent::get('cache');
	}
	
	/**
	 * @return array the cfg-property
	 */
	public function cfg()
	{
		return parent::get('cfg');
	}

	/**
	 * @return PLIB_Template_Handler the cfg-property
	 */
	public function tpl()
	{
		return parent::get('tpl');
	}
	
	/**
	 * @return BS_Session_Manager the sessions-property
	 */
	public function sessions()
	{
		return parent::get('sessions');
	}

	/**
	 * @return BS_User_Current the user-property
	 */
	public function user()
	{
		return parent::get('user');
	}
	
	/**
	 * @return PLIB_MySQL the db-property
	 */
	public function db()
	{
		return parent::get('db');
	}
	
	/**
	 * @return BS_Messages the msgs-property
	 */
	public function msgs()
	{
		return parent::get('msgs');
	}
	
	/**
	 * @return PLIB_Input the input-property
	 */
	public function input()
	{
		return parent::get('input');
	}

	/**
	 * @return PLIB_Cookies the cookies-property
	 */
	public function cookies()
	{
		return parent::get('cookies');
	}

	/**
	 * @return BS_Functions the functions-property
	 */
	public function functions()
	{
		return parent::get('functions');
	}

	/**
	 * @return BS_URL the url-property
	 */
	public function url()
	{
		return parent::get('url');
	}
	
	/**
	 * @return BS_Locale the locale-property
	 */
	public function locale()
	{
		return parent::get('locale');
	}
}
?>