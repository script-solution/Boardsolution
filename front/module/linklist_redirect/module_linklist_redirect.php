<?php
/**
 * Contains the linklist-redirection-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Redirects to a link in the linklist
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_linklist_redirect extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->set_output_enabled(false);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$auth = PLIB_Props::get()->auth();
		$ips = PLIB_Props::get()->ips();
		$doc = PLIB_Props::get()->doc();

		$lid = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		if($lid == null)
		{
			$this->report_error();
			return;
		}
		
		$spam_linkview_on = $auth->is_ipblock_enabled('spam_linkview');
	
		$ip_num = 0;
		if($spam_linkview_on)
			$ip_num = $ips->entry_exists('linkre_'.$lid) ? 1 : 0;
		
		if(!$spam_linkview_on || $ip_num == 0)
			BS_DAO::get_links()->increase_clicks($lid);
		
		$ips->add_entry('linkre_'.$lid);

		$selurl = BS_DAO::get_links()->get_by_id($lid);
		$doc->redirect(PLIB_StringHelper::htmlspecialchars_back($selurl['link_url']));
	}
}
?>