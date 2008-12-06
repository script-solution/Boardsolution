<?php
/**
 * Contains the intro module for the installation
 * 
 * @version			$Id: intro.php 49 2008-07-30 12:35:41Z nasmussen $
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The intro-module
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_1 extends BS_Install_Module
{
	/**
	 * @see FWS_Module::init()
	 *
	 * @param BS_Install_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(1,'forward');
		$renderer->get_action_performer()->perform_action_by_id(1);
	}

	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		// nothing to do here
	}
}
?>