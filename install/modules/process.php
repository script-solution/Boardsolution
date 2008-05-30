<?php
/**
 * Contains the process module for the installation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The process-module
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_Install_process extends BS_Install
{
	public function run()
	{
		$type = $this->functions->get_session_var('install_type');
		
		include_once(PLIB_Path::inner().'install/sql/sql.php');
		if($type == 'full')
		{
			include_once(PLIB_Path::inner().'install/sql/full.php');
			$install = new BS_InstallSQL_full($this);
		}
		else
		{
			include_once(PLIB_Path::inner().'install/sql/update.php');
			$install = new BS_InstallSQL_update($this);
		}
		
		$install->start();
		
		$this->tpl->set_template('step_process.htm',0);
		$this->tpl->add_variables(array(
			'log' => $install->get_log()
		));
		echo $this->tpl->parse_template();
	}
}
?>