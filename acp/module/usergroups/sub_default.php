<?php
/**
 * Contains the default-submodule for usergroups
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the usergroups-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_usergroups_default extends BS_ACP_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->add_action(BS_ACP_ACTION_DELETE_USER_GROUPS,'delete');
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$url = PLIB_Props::get()->url();
		$cache = PLIB_Props::get()->cache();
		$auth = PLIB_Props::get()->auth();
		$tpl = PLIB_Props::get()->tpl();

		if(($delete = $input->get_var('delete','post')) != null)
		{
			$ids = implode(',',$delete);
			$names = array();
			foreach(BS_DAO::get_usergroups()->get_by_ids($delete) as $group)
				$names[] = $group['group_title'];
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				$locale->lang('delete_group_notice').'<br /><br />'
					.sprintf($locale->lang('delete_message'),$namelist),
				$url->get_acpmod_url(0,'&amp;at='.BS_ACP_ACTION_DELETE_USER_GROUPS.'&amp;ids='.$ids),
				$url->get_acpmod_url()
			);
		}
		
		$search = $input->get_var('search','get',PLIB_Input::STRING);
		$helper = BS_ACP_Module_UserGroups_Helper::get_instance();
		$predef_groups = $helper->get_predef_groups();
		
		$groups = array();
		foreach($cache->get_cache('user_groups') as $data)
		{
			if(!$search || stripos($data['group_title'],$search) !== false)
			{
				$groups[] = array(
					'id' => $data['id'],
					'group_name' => $auth->get_colored_groupname($data['id']),
					'is_visible' => BS_ACP_Utils::get_instance()->get_yesno($data['is_visible']),
					'is_super_mod' => BS_ACP_Utils::get_instance()->get_yesno($data['is_super_mod']),
					'is_no_predefined_group' => !in_array($data['id'],$predef_groups)
				);
			}
		}

		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_array('groups',$groups);
		$tpl->add_variables(array(
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
	}
}
?>