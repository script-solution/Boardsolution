<?php
/**
 * Contains the ACP-sub-module-container-class
 * 
 * @version			$Id: submodulecontainer.php 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module-base class for all ACP-sub-module-containers. That means a module
 * that consists of sub-modules.
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_SubModuleContainer extends BS_ACP_Module
{
	/**
	 * The sub-module
	 *
	 * @var BS_ACP_SubModule
	 */
	protected $_sub;
	
	/**
	 * Constructor
	 * 
	 * @param string $module your module-name
	 * @param array $actions the sub-module names that are possible
	 * @param string $default the default sub-module
	 */
	public function __construct($module,$actions = array(),$default = 'default')
	{
		if(count($actions) == 0)
			PLIB_Helper::error('Please provide the possible submodules of this module!');
		
		$action = $this->input->correct_var('action','get',PLIB_Input::STRING,$actions,$default);
		
		// include the sub-module and create it
		include_once(PLIB_Path::inner().'acp/module/'.$module.'/sub_'.$action.'.php');
		$classname = 'BS_ACP_SubModule_'.$module.'_'.$action;
		$this->_sub = new $classname();
	}
	
	public function run()
	{
		$this->_sub->run();
	}
	
	public function error_occurred()
	{
		return parent::error_occurred() || $this->_sub->error_occurred();
	}
	
	public function get_actions()
	{
		return $this->_sub->get_actions();
	}
	
	public function get_template()
	{
		return $this->_sub->get_template();
	}
}
?>