<?php
/**
 * Contains the cfgitem-storage-db class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The db-based implementation for the config-item-storage
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Config_Storage_DB extends PLIB_Object implements PLIB_Config_Storage
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
				$this->_groups[] = new PLIB_Config_Group(
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
	 * @see PLIB_Config_Storage::load_items_with()
	 *
	 * @param string $keyword
	 * @return array
	 */
	public function get_items_with($keyword)
	{
		$locale = PLIB_Props::get()->locale();

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
		
		return PLIB_String::strpos(PLIB_String::strtolower($haystack),$needle) !== false;
	}

	public function restore_default($id)
	{
		BS_DAO::get_config()->revert_setting($id);
	}

	public function store($id,$value)
	{
		BS_DAO::get_config()->update_setting($id,$value);
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>