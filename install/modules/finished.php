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
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_Install_finished extends BS_Install
{
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$functions = FWS_Props::get()->functions();

		$tpl->set_template('step_finished.htm');
		$tpl->add_variables(array(
			'type' => $functions->get_session_var('install_type')
		));
		echo $tpl->parse_template();
		
		$functions->clear_session();
	}
}
?>