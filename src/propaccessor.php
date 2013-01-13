<?php
/**
 * Contains the property-accessor-class
 * 
 * @package			Boardsolution
 * @subpackage	src
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

/**
 * The property-accessor for Boardsolution. We change and add some properties to the predefined
 * ones.
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_PropAccessor extends FWS_PropAccessor
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
			FWS_Helper::def_error('instance','doc','BS_Document',$doc);
		
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
	 * @return FWS_Cache_Container the cache-property
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
	 * @return FWS_Template_Handler the cfg-property
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
	 * @return FWS_DB_MySQL_Connection the db-property
	 */
	public function db()
	{
		return $this->get('db');
	}
	
	/**
	 * @return FWS_Input the input-property
	 */
	public function input()
	{
		return $this->get('input');
	}

	/**
	 * @return FWS_Cookies the cookies-property
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
	 * @return BS_Locale the locale-property
	 */
	public function locale()
	{
		return $this->get('locale');
	}
}
?>