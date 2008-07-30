<?php
/**
 * Contains the acp-base-document-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base-document for all ACP-documents
 *
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_Document extends BS_Document
{
	/**
	 * @see PLIB_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
	{
		// set a fix path to the acp-templates
		$tpl = PLIB_Props::get()->tpl();
		$tpl->set_path('acp/templates/');
		$tpl->add_allowed_method('gurl','get_acpmod_url');
		
		$user = PLIB_Props::get()->user();
		$tpl->add_global('gisloggedin',$user->is_loggedin());
		$tpl->add_global('glang',$user->get_language());
		
		$locale = PLIB_Props::get()->locale();
		$locale->add_language_file('admin');
		
		parent::prepare_rendering();
	}
}
?>