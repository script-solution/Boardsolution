<?php
/**
 * Contains the finished module for the installation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The finished-module
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_6 extends BS_Install_Module
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
	}

	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();

		$tpl->add_variables(array(
			'type' => $user->get_session_data('install_type')
		));
		
		$user->clear_session_data();
	}
}
?>