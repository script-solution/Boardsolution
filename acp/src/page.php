<?php
/**
 * Contains acp-page-class
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The base page-class for all acp-pages
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_ACP_Page extends BS_Page
{
	/**
	 * @see PLIB_Page::_before_start()
	 */
	protected function before_start()
	{
		parent::before_start();
		
		// set a fix path to the acp-templates
		$tpl = PLIB_Props::get()->tpl();
		$tpl->set_path('acp/templates/');
		$tpl->add_allowed_method('gurl','get_acpmod_url');
		
		$user = PLIB_Props::get()->user();
		$tpl->add_global('gisloggedin',$user->is_loggedin());
		$tpl->add_global('glang',$user->get_language());
		
		$locale = PLIB_Props::get()->locale();
		$locale->add_language_file('admin');
	}

	protected function load_action_perf()
	{
		return new BS_ACP_Action_Performer();
	}
}
?>