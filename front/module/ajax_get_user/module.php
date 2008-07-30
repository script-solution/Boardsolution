<?php
/**
 * Contains the module-class for the ajax-user-search
 * 
 * @version			$Id: module_ajax_get_user.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_raw_renderer();
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		$input = PLIB_Props::get()->input();
		$doc = PLIB_Props::get()->doc();

		// the user has to have access to the memberlist to get existing usernames
		if($cfg['enable_memberlist'] == 1 && $auth->has_global_permission('view_memberlist'))
		{
			$keyword = $input->get_var('kw','get',PLIB_Input::STRING);
			
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
			
			$result = PLIB_StringHelper::htmlspecialchars_back(implode(',',$found_user));
			$renderer = $doc->use_raw_renderer();
			$renderer->set_content($result);
		}
	}
}
?>