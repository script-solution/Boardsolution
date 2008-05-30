<?php
/**
 * Contains the linklist-module
 * 
 * @version			$Id$
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
	public function __construct()
	{
		parent::__construct('linklist',array('default','add'),'default');
	}
	
	public function get_location()
	{
		$loc = array(
			$this->locale->lang('linklist') => $this->url->get_url()
		);
		return array_merge($loc,$this->_sub->get_location());
	}
	
	public function has_access()
	{
		return $this->cfg['enable_linklist'] == 1 && $this->auth->has_global_permission('view_linklist');
	}
}
?>