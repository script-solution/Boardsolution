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
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();

		$type = $functions->get_session_var('install_type');
		
		include_once(FWS_Path::server_app().'install/sql/sql.php');
		if($type == 'full')
		{
			include_once(FWS_Path::server_app().'install/sql/full.php');
			$install = new BS_InstallSQL_full($this);
		}
		else
		{
			include_once(FWS_Path::server_app().'install/sql/update.php');
			$install = new BS_InstallSQL_update($this);
		}
		
		$install->start();
		
		$tpl->set_template('step_process.htm');
		$tpl->add_variables(array(
			'log' => $install->get_log()
		));
		echo $tpl->parse_template();
	}
}
?>