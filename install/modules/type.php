<?php
/**
 * Contains the type module for the installation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The type-module
 * 
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class BS_Install_type extends BS_Install
{
	public function run()
	{
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		$type = $functions->get_session_var('install_type');
		$options = array(
			'full' => $locale->lang('fullinstall'),
			'update' => $locale->lang('update')
		);
		
		$tpl->set_template('step_type.htm');
		$tpl->add_variables(array(
			'install_type_combo' => $this->html->get_combobox($options,$type,'install_type')
		));
		echo $tpl->parse_template();
	}
}
?>