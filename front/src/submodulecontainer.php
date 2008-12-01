<?php
/**
 * Contains the front-sub-module-container-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module-base class for all front-sub-module-containers. That means a module
 * that consists of sub-modules.
 * 
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_SubModuleContainer extends BS_Front_Module
{
	/**
	 * The sub-module
	 *
	 * @var BS_Front_SubModule
	 */
	protected $_sub;
	
	/**
	 * The init
	 * 
	 * @param string $module your module-name
	 * @param array $submodules the sub-module names that are possible
	 * @param string $default the default sub-module
	 */
	public function __construct($module,$submodules = array(),$default = 'default')
	{
		$input = FWS_Props::get()->input();

		if(count($submodules) == 0)
			FWS_Helper::error('Please provide the possible submodules of this module!');
		
		$sub = $input->correct_var(BS_URL_SUB,'get',FWS_Input::STRING,$submodules,$default);
		
		// include the sub-module and create it
		include_once(FWS_Path::server_app().'front/module/'.$module.'/sub_'.$sub.'.php');
		$classname = 'BS_Front_SubModule_'.$module.'_'.$sub;
		$this->_sub = new $classname();
	}

	/**
	 * @see FWS_Module::error_occurred()
	 *
	 * @return boolean
	 */
	public function error_occurred()
	{
		return parent::error_occurred() || $this->_sub->error_occurred();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$tpl->set_template($this->_sub->get_template());
		$this->_sub->run();
		$tpl->restore_template();
	}
}
?>