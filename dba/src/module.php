<?php
/**
 * Contains the module for the dbbackup-script
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module-base class for all dbbackup-modules
 * 
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_DBA_Module extends FWS_Module
{
	/**
	 * @see FWS_Module::request_formular()
	 *
	 * @return BS_HTML_Formular
	 */
	protected final function request_formular()
	{
		$tpl = FWS_Props::get()->tpl();

		$form = new BS_HTML_Formular(false,false);
		$tpl->add_variable_ref('form',$form);
		$tpl->add_allowed_method('form','*');
		return $form;
	}
}
?>