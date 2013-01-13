<?php
/**
 * Contains the linklist-redirection-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_raw_renderer();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$ips = FWS_Props::get()->ips();
		$doc = FWS_Props::get()->doc();

		$lid = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
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
		$doc->redirect(FWS_StringHelper::htmlspecialchars_back($selurl['link_url']));
	}
}
?>