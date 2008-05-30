<?php
/**
 * Contains the helper-class for the module ACPAccess
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An helper-class for the module ACPAccess of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_ACPAccess_Helper extends PLIB_Singleton
{
	/**
	 * @return BS_ACP_Module_ACPAccess_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}

	/**
	 * builds the options for the group-combobox
	 *
	 * @return array an associative array with the user-groups (except admins and guests)
	 */
	public function get_group_options()
	{
		$options = array();
		foreach($this->cache->get_cache('user_groups') as $row)
		{
			if($row['id'] != BS_STATUS_GUEST && $row['id'] != BS_STATUS_ADMIN)
				$options[$row['id']] = $row['group_title'];
		}
		return $options;
	}

	/**
	 * determines the name of the given module
	 *
	 * @param string $module the module
	 * @return string the name of the module (the LANG-entry)
	 */
	public function get_module_name($module)
	{
		foreach(BS_ACP_Menu::get_instance()->get_menu_items() as $group)
		{
			foreach($group['modules'] as $mod => $data)
			{
				if($mod == $module)
					return $data['title'];
			}
		}

		return '';
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>