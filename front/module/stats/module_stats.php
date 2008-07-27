<?php
/**
 * Contains the stats-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The stats-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_stats extends BS_Front_SubModuleContainer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('stats',array('default','timeline'));
	}
	
	/**
	 * @see BS_Front_SubModuleContainer::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		
		$doc->add_breadcrumb($locale->lang('statistics'),'');
		$doc->set_has_access($cfg['enable_stats'] == 1 && $auth->has_global_permission('view_stats'));
		
		// init submodule
		$this->_sub->init($doc);
		
		$doc->set_template($this->_sub->get_template());
	}
}
?>