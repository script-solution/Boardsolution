<?php
/**
 * Contains the module-class for the ajax-user-search
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
 * Looks for matching usernames. This will be called via AJAX
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_ajax_get_user extends BS_Front_Module
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
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$input = FWS_Props::get()->input();
		$doc = FWS_Props::get()->doc();

		// the user has to have access to the memberlist to get existing usernames
		if($cfg['enable_memberlist'] == 1 && $auth->has_global_permission('view_memberlist'))
		{
			$keyword = $input->get_var('kw','get',FWS_Input::STRING);
			
			// limit the search to 6
			$found_user = array();
			$users = BS_DAO::get_user()->get_users_like_name($keyword,6);
			$count = min(5,count($users));
			// add max. 5 user
			for($i = 0;$i < $count;$i++)
				$found_user[] = $users[$i]['user_name'];
			
			// we limit the number of user to 5 and select 6
			// so if we have more than 5, we add the "..."
			if(count($users) > 5)
				$found_user[] = '...';
			
			$result = FWS_StringHelper::htmlspecialchars_back(implode(',',$found_user));
			$renderer = $doc->use_raw_renderer();
			$renderer->set_content($result);
		}
	}
}
?>