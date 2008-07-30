<?php
/**
 * Contains the intro module for the installation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The intro-module
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_Install_intro extends BS_Install
{
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();

		$tpl->set_template('step_intro.htm');
		echo $tpl->parse_template();
	}
}
?>