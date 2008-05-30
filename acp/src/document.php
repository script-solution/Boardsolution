<?php
/**
 * Contains acp-document-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The document-class for all acp-pages
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_Document extends BS_Document
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->locale->add_language_file('admin');
	}
	
	protected function _load_action_perf()
	{
		return new BS_ACP_Action_Performer();
	}

	protected function _load_user()
	{
		$c = parent::_load_user();
		// we don't want to login via cookie in the acp
		$c->set_use_cookies(false);
		return $c;
	}

	protected function _load_tpl()
	{
		$c = parent::_load_tpl();
		// set a fix path to the acp-templates
		$c->set_path(PLIB_Path::inner().'acp/templates/');
		$c->add_allowed_method('gurl','get_acpmod_url');
		return $c;
	}
}
?>