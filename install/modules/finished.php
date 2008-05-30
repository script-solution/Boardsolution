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
		$this->tpl->set_template('step_finished.htm',0);
		$this->tpl->add_variables(array(
			'type' => $this->functions->get_session_var('install_type')
		));
		echo $this->tpl->parse_template();
		
		$this->functions->clear_session();
	}
}
?>