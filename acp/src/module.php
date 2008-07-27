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
	 * Creates the formular, adds it to the template and allows all methods of it
	 * to be called.
	 *
	 * @return BS_HTML_Formular the created formular
	 */
	protected final function request_formular()
	{
		$tpl = PLIB_Props::get()->tpl();

		$form = new BS_HTML_Formular(false,false);
		$tpl->add_array('form',$form);
		$tpl->add_allowed_method('form','*');
		return $form;
	}
	
	/**
	 * Reports an error and stores that the module has not finished in a correct way.
	 * Note that you have to specify a message if the type is no error and no no-access-msg!
	 *
	 * @param int $type the type. see PLIB_Messages::MSG_TYPE_*
	 * @param string $message you can specify the message to display here, if you like
	 */
	protected final function report_error($type = PLIB_Messages::MSG_TYPE_ERROR,$message = '')
	{
		$doc = PLIB_Props::get()->doc();
		$doc->report_error($type,$message);
	}
}
?>