<?php
/**
 * Contains the helper-class for the settings
 *
 * @version			$Id: helper.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An helper-class for the cfgitems-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Config_Helper extends PLIB_Singleton
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
	 * @var PLIB_Config_Manager
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
		$tplcells = array();
		foreach($this->get_manager()->get_groups() as $group)
		{
			if($group->get_parent_id() == 0)
			{
				$desc = $this->locale->contains_lang($group->get_name().'_desc');
				$tplcells[] = array(
					'id' => $group->get_id(),
					'class' => $group->get_id() == $gid ? 'a_coldesc' : 'a_main',
					'title' => $this->locale->lang($group->get_title(),false),
					'description' => $desc ? $this->locale->lang($group->get_name().'_desc') : ''
				);
			}
		}
		return PLIB_Array_Utils::convert_to_2d($tplcells,$perline);
	}
	
	/**
	 * @return PLIB_Config_Manager the manager
	 */
	public function get_manager()
	{
		return $this->_manager;
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>