<?php
/**
 * Contains the standalone-class for the ajax-user-search
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Looks for matching usernames. This will be called via AJAX
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_ajax_check_user extends BS_Standalone
{
	public function run()
	{
		// the user has to have access to the memberlist to get existing usernames
		if($this->cfg['enable_memberlist'] == 1 && $this->auth->has_global_permission('view_memberlist'))
		{
			$keyword = $this->input->get_var('kw','get',PLIB_Input::STRING);
			
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
			
			echo PLIB_StringHelper::htmlspecialchars_back(implode(',',$found_user));
		}
	}
}
?>