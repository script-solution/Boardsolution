<?php
/**
 * Contains the process module for the installation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The process-module
 * 
 * @package			Boardsolution
 * @subpackage	install.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Module_5 extends BS_Install_Module
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
		$renderer->add_action(5,'forward');
		$renderer->get_action_performer()->perform_action_by_id(5);
		
		$this->connect_to_db();
	}

	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();

		$type = $user->get_session_data('install_type','full');
		if($type == 'full')
			$install = new BS_Install_Module_5_SQL_Full();
		else
			$install = new BS_Install_Module_5_SQL_Update();
		
		$install->start();
		
		$tpl->add_variables(array(
			'log' => $install->get_log()
		));
	}
}
?>