<?php
/**
 * Contains the intro module for the installation
 * 
 * @version			$Id: intro.php 543 2008-04-10 07:32:51Z nasmussen $
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
		$this->tpl->set_template('step_intro.htm',0);
		echo $this->tpl->parse_template();
	}
}
?>