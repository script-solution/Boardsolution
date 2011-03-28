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
	 * @see FWS_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
	{
		// set a fix path to the acp-templates
		$tpl = FWS_Props::get()->tpl();
		$tpl->set_path('acp/templates/');
		$tpl->add_allowed_method('gurl','simple_acp_url');
		
		$locale = FWS_Props::get()->locale();
		$locale->add_language_file('admin');
		
		BS_URL::set_append_extern_vars(false);
		
		parent::prepare_rendering();
	}
}
?>