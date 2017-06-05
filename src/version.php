<?php
/**
 * Contains the version-class
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
 * Represents a version for the version-comparation
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Version extends FWS_Object
{
	/**
	 * The host which provides the versions-file
	 */
	const VERSION_HOST = 'www.script-solution.de';
	
	/**
	 * The path of the versions-file
	 */
	const VERSION_PATH = '/bsversions/versions.xml';
	
	/**
	 * Represents the type "update"
	 */
	const UPDATE = 'update';
	
	/**
	 * Represents the type "release"
	 */
	const RELEASE = 'release';
	
	/**
	 * Represents the type "unknown"
	 */
	const UNKNOWN = 'unknown';
	
	/**
	 * Reads the versions from the given XML-document
	 *
	 * @param string $xml the XML-document as string
	 * @return array all found versions
	 */
	public static function read_versions($xml)
	{
		$versions = array();
		$doc = new SimpleXMLElement($xml);
		foreach($doc->version as $v)
		{
			$versions[] = new self(
				(string)$v['id'],(string)$v->name,(string)$v->type,(string)$v->date,(string)$v->instructions
			);
		}
		return $versions;
	}
	
	/**
	 * Checks wether there is an update
	 *
	 * @param array $versions an array of BS_Version instances
	 * @return array all updates that are available or just the release if there is any or NULL
	 */
	public static function check_for_update($versions)
	{
		usort($versions,array('BS_Version','_sort_versions'));
		$newest = $versions[count($versions) - 1];
		$current = new self(BS_VERSION_ID,BS_VERSION);
		if($current->compare($newest) < 0)
		{
			// new release?
			if($newest->get_release_number() > $current->get_release_number())
				return $newest;
			
			// collect updates
			$updates = array($newest);
			for($i = count($versions) - 2;$i >= 0;$i--)
			{
				if($current->compare($versions[$i]) >= 0)
					break;
				$updates[] = $versions[$i];
			}
			return array_reverse($updates);
		}
		return null;
	}
	
	/**
	 * Compare-function for versions
	 *
	 * @param BS_Version $a the first version
	 * @param BS_Version $b the second version
	 * @return int the compare-result
	 */
	private static function _sort_versions($a,$b)
	{
		return $a->compare($b);
	}
	
	/**
	 * The version-id
	 *
	 * @var string
	 */
	private $_id;
	
	/**
	 * The version-name
	 *
	 * @var string
	 */
	private $_name;
	
	/**
	 * The version-type (self::UPDATE or self::RELEASE)
	 *
	 * @var string
	 */
	private $_type;
	
	/**
	 * The date of the version (YYYY-MM-DD)
	 *
	 * @var string
	 */
	private $_date;
	
	/**
	 * An URL to instructions for the update
	 *
	 * @var string
	 */
	private $_instructions;
	
	/**
	 * Constructor
	 *
	 * @param string $id the version-id
	 * @param string $name the version-name
	 * @param string $type the type: self::UPDATE or self::RELEASE
	 * @param string $date the date (YYYY-MM-DD)
	 * @param string $instructions an URL to instructions for the update
	 */
	public function __construct($id,$name,$type = self::UNKNOWN,$date = '0000-00-00',$instructions = '')
	{
		parent::__construct();
		
		$this->_id = $id;
		$this->_name = $name;
		$this->_type = $type;
		$this->_date = $date;
		$this->_instructions = $instructions;
	}
	
	/**
	 * @return int the release-number, so for example "14"
	 */
	public function get_release_number()
	{
		return (int)FWS_String::substr($this->_id,0,2);
	}
	
	/**
	 * @return int the testnumber-number (0 if it is no beta)
	 */
	public function get_testnumber_number()
	{
		$matches = array();
		if(preg_match('/^\d{3}[ab]{1}(\d+)$/',$this->_id,$matches))
			return $matches[1];
		return 0;
	}
	
	/**
	 * Compares $this to the given version
	 *
	 * @param BS_Version $version the version
	 * @return int 0 if the versions are equal, < 0 if $this is older, > 0 if $this is newer
	 */
	public function compare($version)
	{
		// cut alpha number
		$tvno = strtok($this->_id,'a');
		$cvno = strtok($version->get_id(),'a');
		
		// cut beta number
		$tvno = strtok($tvno,'b');
		$cvno = strtok($cvno,'b');
		if($tvno == $cvno)
		{
			$tb = $this->get_testnumber_number();
			$cb = $version->get_testnumber_number();
			if($tb == 0 && $cb == 0)
				return 0;
			if($tb > 0 && $cb == 0)
				return -1;
			if($tb == 0 && $cb > 0)
				return 1;
			return $tb - $cb;
		}
		return $tvno - $cvno;
	}

	/**
	 * @return string the date (YYYY-MM-DD)
	 */
	public function get_date()
	{
		return $this->_date;
	}

	/**
	 * @return string the id
	 */
	public function get_id()
	{
		return $this->_id;
	}

	/**
	 * @return string the name
	 */
	public function get_name()
	{
		return $this->_name;
	}

	/**
	 * @return string the type (self::UPDATE or self::RELEASE)
	 */
	public function get_type()
	{
		return $this->_type;
	}

	/**
	 * @return string the URL for instructions (may be empty)
	 */
	public function get_instructions()
	{
		return $this->_instructions;
	}

	/**
	 * @see FWS_Object::get_dump_vars()
	 *
	 * @return array
	 */
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>