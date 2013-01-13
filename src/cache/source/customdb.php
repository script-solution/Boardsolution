<?php
/**
 * Contains the custom-db-implementation for the source
 * 
 * @package			Boardsolution
 * @subpackage	src.cache
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
 * A db-based implementation for the source which lets you specify the query manually.
 *
 * @package			Boardsolution
 * @subpackage	src.cache
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Cache_Source_CustomDB extends FWS_Object implements FWS_Cache_Source
{
	/**
	 * The SQL-query
	 *
	 * @var string
	 */
	private $_sql;
	
	/**
	 * The key of the table (null = none)
	 *
	 * @var string
	 */
	private $_key;
	
	/**
	 * Constructor
	 * 
	 * @param string $sql the SQL-query
	 * @param string $key the key of the table (null = none)
	 */
	public function __construct($sql,$key = null)
	{
		parent::__construct();
		
		if(empty($sql))
			FWS_Helper::def_error('notempty','sql',$sql);
		if($key !== null && empty($key))
			FWS_Helper::error('$key is not null but empty!');
		
		$this->_sql = $sql;
		$this->_key = $key;
	}
	
	public function get_content()
	{
		$db = FWS_Props::get()->db();
		$rows = array();
		foreach($db->execute($this->_sql) as $row)
		{
			if($this->_key !== null)
				$rows[$row[$this->_key]] = $row;
			else
				$rows[] = $row;
		}
		return $rows;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>