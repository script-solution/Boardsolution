<?php
/**
 * Contains the linklist-module
 * 
 * @version			$Id: module_linklist.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The linklist-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_linklist extends BS_Front_SubModuleContainer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('linklist',array('default','add'),'default');
	}
	
	/**
	 * @see BS_Front_SubModuleContainer::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($cfg['enable_linklist'] == 1 && $auth->has_global_permission('view_linklist'));
		$renderer->add_breadcrumb($locale->lang('linklist'),$url->get_url());
		
		// init submodule
		$this->_sub->init($doc);
		
		$renderer->set_template($this->_sub->get_template());
	}
}
?>