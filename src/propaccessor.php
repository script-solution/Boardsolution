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
	 * The document-instance
	 *
	 * @var BS_Document
	 */
	private $_doc;
	
	/**
	 * @return BS_Document the document
	 */
	public function doc()
	{
		return $this->_doc;
	}
	
	/**
	 * Sets the document-instance
	 *
	 * @param BS_Document $doc the new value
	 */
	public function set_doc($doc)
	{
		if(!($doc instanceof BS_Document))
			PLIB_Helper::def_error('instance','doc','BS_Document',$doc);
		
		$this->_doc = $doc;
	}
	
	/**
	 * @return BS_Auth the auth-property
	 */
	public function auth()
	{
		return $this->get('auth');
	}
	
	/**
	 * @return BS_IPs the ips-property
	 */
	public function ips()
	{
		return $this->get('ips');
	}
	
	/**
	 * @return BS_Unread the unread-property
	 */
	public function unread()
	{
		return $this->get('unread');
	}
	
	/**
	 * @return BS_Forums_Manager the forums-property
	 */
	public function forums()
	{
		return $this->get('forums');
	}
	
	/**
	 * @return PLIB_Cache_Container the cache-property
	 */
	public function cache()
	{
		return $this->get('cache');
	}
	
	/**
	 * @return array the cfg-property
	 */
	public function cfg()
	{
		return $this->get('cfg');
	}

	/**
	 * @return PLIB_Template_Handler the cfg-property
	 */
	public function tpl()
	{
		return $this->get('tpl');
	}
	
	/**
	 * @return BS_Session_Manager the sessions-property
	 */
	public function sessions()
	{
		return $this->get('sessions');
	}

	/**
	 * @return BS_User_Current the user-property
	 */
	public function user()
	{
		return $this->get('user');
	}
	
	/**
	 * @return PLIB_MySQL the db-property
	 */
	public function db()
	{
		return $this->get('db');
	}
	
	/**
	 * @return PLIB_Input the input-property
	 */
	public function input()
	{
		return $this->get('input');
	}

	/**
	 * @return PLIB_Cookies the cookies-property
	 */
	public function cookies()
	{
		return $this->get('cookies');
	}

	/**
	 * @return BS_Functions the functions-property
	 */
	public function functions()
	{
		return $this->get('functions');
	}

	/**
	 * @return BS_URL the url-property
	 */
	public function url()
	{
		return $this->get('url');
	}
	
	/**
	 * @return BS_Locale the locale-property
	 */
	public function locale()
	{
		return $this->get('locale');
	}
}
?>