<?php
/**
 * Contains the ACP-sub-module-container-class
 * 
 * @version			$Id$
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
		$input = FWS_Props::get()->input();

		if(count($actions) == 0)
			FWS_Helper::error('Please provide the possible submodules of this module!');
		
		$action = $input->correct_var('action','get',FWS_Input::STRING,$actions,$default);
		
		// include the sub-module and create it
		include_once(FWS_Path::server_app().'acp/module/'.$module.'/sub_'.$action.'.php');
		$classname = 'BS_ACP_SubModule_'.$module.'_'.$action;
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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		
		$classname = get_class($this->_sub);
		$lastus = strrpos($classname,'_');
		$prevlastus = strrpos(FWS_String::substr($classname,0,$lastus),'_');
		$renderer->set_template(FWS_String::strtolower(FWS_String::substr($classname,$prevlastus + 1)).'.htm');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$this->_sub->run();
	}
}
?>