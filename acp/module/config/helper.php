<?php
/**
 * Contains the helper-class for the settings
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
 * An helper-class for the cfgitems-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Config_Helper extends FWS_Singleton
{
	/**
	 * @return BS_ACP_Module_Config_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * The config-manager
	 *
	 * @var FWS_Config_Manager
	 */
	private $_manager;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$storage = new BS_ACP_Module_Config_Storage_DB();
		$this->_manager = new BS_ACP_Module_Config_Manager($storage);
	}
	
	/**
	 * Returns the groups for the template
	 *
	 * @param int $gid the selected group
	 * @param int $perline the number of groups per line
	 * @return array the groups
	 */
	public function get_groups($gid,$perline)
	{
		$locale = FWS_Props::get()->locale();

		$tplcells = array();
		foreach($this->get_manager()->get_groups() as $group)
		{
			if($group->get_parent_id() == 0)
			{
				$desc = $locale->contains_lang($group->get_name().'_desc');
				$tplcells[] = array(
					'id' => $group->get_id(),
					'class' => $group->get_id() == $gid ? 'a_coldesc' : 'a_main',
					'title' => $locale->lang($group->get_title(),false),
					'description' => $desc ? $locale->lang($group->get_name().'_desc') : ''
				);
			}
		}
		return FWS_Array_Utils::convert_to_2d($tplcells,$perline);
	}
	
	/**
	 * @return FWS_Config_Manager the manager
	 */
	public function get_manager()
	{
		return $this->_manager;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>