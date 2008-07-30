<?php
/**
 * Contains the massemail module for the ACP
 * 
 * @version			$Id: module_massemail.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The massemail-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_massemail extends BS_ACP_SubModuleContainer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('massemail',array('default','send'),'default');
	}

	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();

		$renderer->add_breadcrumb($locale->lang('acpmod_massemail'),$url->get_acpmod_url());
		
		// init submodule
		$this->_sub->init($doc);
	}
}
?>