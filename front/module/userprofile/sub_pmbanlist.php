<?php
/**
 * Contains the pmbanlist-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pmbanlist submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pmbanlist extends BS_Front_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACTION_BAN_USER,'pmbanuser');
		$doc->add_action(BS_ACTION_UNBAN_USER,'pmunbanuser');

		$doc->add_breadcrumb($locale->lang('banlist'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=pmbanlist'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$functions = PLIB_Props::get()->functions();
		$tpl = PLIB_Props::get()->tpl();

		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}

		$del = $input->get_var('del','post');
		if($del != null && PLIB_Array_Utils::is_integer($del))
		{
			$ids = implode(',',$del);
			$names = array();
			foreach(BS_DAO::get_userbans()->get_by_user($user->get_user_id(),$del) as $i => $data)
				$names[] = $data['user_name'];
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));

			$loc = '&amp;'.BS_URL_LOC.'=pmbanlist';
			$yes_url = $url->get_url(0,$loc.'&amp;'.BS_URL_AT.'='
				.BS_ACTION_UNBAN_USER.'&amp;'.BS_URL_DEL.'='.$ids,'&amp;',true);
			$no_url = $url->get_url(0,$loc);
			$target_url = $url->get_url('redirect','&amp;'.BS_URL_LOC.'=del_pm_ban'
				.'&amp;'.BS_URL_ID.'='.$ids);
			
			$functions->add_delete_message(
				sprintf($locale->lang('banlist_delete'),$namelist),$yes_url,$no_url,$target_url
			);
		}

		$tpl->add_variables(array(
			'action_param' => BS_URL_ACTION
		));
		
		$banned_user = array();
		foreach(BS_DAO::get_userbans()->get_all_of_user($user->get_user_id()) as $i => $data)
		{
			$banned_user[] = array(
				'number' => $i + 1,
				'user_name' => BS_UserUtils::get_instance()->get_link($data['baned_user'],$data['user_name'],
					$data['user_group']),
				'id' => $data['id']
			);
		}
		
		$tpl->add_array('banned_user',$banned_user);
	
		$tpl->add_variables(array(
			'ban_user_url' => $url->get_url(
				'userprofile',
				'&amp;'.BS_URL_LOC.'=pmbanlist'.'&amp;'.BS_URL_AT.'='.BS_ACTION_BAN_USER,
				'&amp;',
				true
			)
		));
	}
}
?>