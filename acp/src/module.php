<?php
/**
 * Contains the ACP-module-base-class
 * 
 * @version			$Id: module.php 676 2008-05-08 09:02:28Z nasmussen $
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
	protected final function _request_formular()
	{
		$form = new BS_HTML_Formular(false,false);
		$this->tpl->add_array('form',$form);
		$this->tpl->add_allowed_method('form','*');
		return $form;
	}
}
?>