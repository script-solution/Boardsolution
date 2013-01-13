<?php
/**
 * Contains the cfgitem-storage-db class
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
 * The db-based implementation for the config-item-storage
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Config_Storage_DB extends FWS_Object implements FWS_Config_Storage
{
	/**
	 * The groups
	 *
	 * @var array
	 */
	private $_groups = null;
	
	public function get_groups()
	{
		if($this->_groups === null)
		{
			$this->_groups = array();
			foreach(BS_DAO::get_cfggroups()->get_all() as $data)
			{
				$this->_groups[] = new FWS_Config_Group(
					$data['id'],$data['parent_id'],$data['name'],$data['title'],$data['sort']
				);
			}
		}
		
		return $this->_groups;
	}
	
	public function get_items_of_group($id)
	{
		$result = array();
		foreach(BS_DAO::get_config()->get_by_group($id) as $data)
			$result[] = new BS_ACP_Module_Config_Data($data);
		return $result;
	}

	/**
	 * @see FWS_Config_Storage::load_items_with()
	 *
	 * @param string $keyword
	 * @return array
	 */
	public function get_items_with($keyword)
	{
		$locale = FWS_Props::get()->locale();

		$result = array();
		foreach(BS_DAO::get_config()->get_all() as $data)
		{
			$add = false;
			if($this->_contains($locale->lang($data['name'],false),$keyword))
				$add = true;
			else if($locale->contains_lang($data['name'].'_desc') &&
				$this->_contains($locale->lang($data['name'].'_desc'),$keyword))
				$add = true;
			else if($this->_contains($data['value'],$keyword))
				$add = true;
			
			if($add)
				$result[] = new BS_ACP_Module_Config_Data($data);
		}
		return $result;
	}
	
	/**
	 * Checks wether <var>$haystack</var> contains <var>$needle</var>. <var>$needle</var> will
	 * be considered as lower-case.
	 *
	 * @param string $haystack your search-subject
	 * @param string $needle your keyword
	 * @return boolean true if it contains the keyword
	 */
	private function _contains($haystack,$needle)
	{
		if($needle == '')
			return true;
		
		return FWS_String::strpos(FWS_String::strtolower($haystack),$needle) !== false;
	}

	public function restore_default($id)
	{
		BS_DAO::get_config()->revert_setting($id);
	}

	public function store($id,$value)
	{
		BS_DAO::get_config()->update_setting($id,$value);
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>