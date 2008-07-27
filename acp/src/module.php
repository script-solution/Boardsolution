<?php
/**
 * Contains the ACP-module-base-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module-base class for all ACP-modules
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_Module extends PLIB_Module
{
	/**
	 * @see PLIB_Module::request_formular()
	 *
	 * @return BS_HTML_Formular
	 */
	protected final function request_formular()
	{
		$tpl = PLIB_Props::get()->tpl();

		$form = new BS_HTML_Formular(false,false);
		$tpl->add_array('form',$form);
		$tpl->add_allowed_method('form','*');
		return $form;
	}
}
?>