<?php
/**
 * Contains the standalone-class for the linklist-redirection
 * 
 * @version			$Id: linklist_redirect.php 725 2008-05-22 15:48:16Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Redirects to a link in the linklist
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_linklist_redirect extends BS_Standalone
{
	public function run()
	{
		$lid = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		if($lid != null)
		{
			$spam_linkview_on = $this->auth->is_ipblock_enabled('spam_linkview');
	
			$ip_num = 0;
			if($spam_linkview_on)
				$ip_num = $this->ips->entry_exists('linkre_'.$lid) ? 1 : 0;
			
			if(!$spam_linkview_on || $ip_num == 0)
				BS_DAO::get_links()->increase_clicks($lid);
			
			$this->ips->add_entry('linkre_'.$lid);
	
			$selurl = BS_DAO::get_links()->get_by_id($lid);
			$this->doc->redirect(PLIB_StringHelper::htmlspecialchars_back($selurl['link_url']));
		}
	}
}
?>