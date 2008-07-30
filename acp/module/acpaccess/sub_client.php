<?php
/**
 * Contains the client-submodule for acpaccess
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The client sub-module for the acpaccess-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_acpaccess_client extends BS_ACP_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$cache = PLIB_Props::get()->cache();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_ACPACCESS_GROUP,array('client','group'));
		$renderer->add_action(BS_ACP_ACTION_ACPACCESS_USER,array('client','user'));

		$type = $input->get_var('type','get',PLIB_Input::STRING);
		$murl = $url->get_acpmod_url(0,'&amp;action=client&amp;type='.$type);
		
		if($type == 'user')
		{
			$username = $this->_get_username();
			$renderer->add_breadcrumb(
				sprintf($locale->lang('permissions_for_user'),$username),
				$murl.'&amp;name='.$username
			);
		}
		else
		{
			$group = $this->_get_group();
			$gdata = $cache->get_cache('user_groups')->get_element($group);
			if($gdata !== null)
			{
				$renderer->add_breadcrumb(
					sprintf($locale->lang('permissions_for_group'),$gdata['group_title']),
					$murl.'&amp;group='.$group
				);
			}
		}
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$auth = PLIB_Props::get()->auth();
		$locale = PLIB_Props::get()->locale();
		$cache = PLIB_Props::get()->cache();
		$tpl = PLIB_Props::get()->tpl();

		$type = $input->get_var('type','get',PLIB_Input::STRING);
		$group = $this->_get_group();
		$username = $this->_get_username();
		
		if($type == 'user')
		{
			$data = BS_DAO::get_profile()->get_user_by_name($username);
			if($data === false || $auth->is_in_group($data['user_group'],BS_STATUS_ADMIN))
			{
				$this->report_error(PLIB_Document_Messages::ERROR,$locale->lang('user_not_found'));
				return;
			}

			$title = sprintf($locale->lang('permissions_for_user'),$username);
			$col_title = $locale->lang('current_module_permission');
			$atype = 'user';
			$aval = $data['id'];
			$usergroups = PLIB_Array_Utils::advanced_explode(',',$data['user_group']);
			$action_type = BS_ACP_ACTION_ACPACCESS_USER;
		}
		else
		{
			$gdata = $cache->get_cache('user_groups')->get_element($group);
			if($gdata === null || $group == BS_STATUS_ADMIN || $group == BS_STATUS_GUEST)
			{
				$this->report_error();
				return;
			}

			$title = sprintf($locale->lang('permissions_for_group'),$gdata['group_title']);
			$col_title = $locale->lang('current_user_permissions');
			$atype = 'group';
			$aval = $group;
			$action_type = BS_ACP_ACTION_ACPACCESS_GROUP;
		}

		$tpl->add_variables(array(
			'action_type' => $action_type,
			'aval' => $aval,
			'user_group' => $group,
			'user_name' => $username,
			'type' => $type,
			'title' => $title,
			'current_permission_col_title' => $col_title
		));
		
		$this->request_formular(false,false);
		$acpaccess = $cache->get_cache('acp_access');

		// display modules
		$categories = array();
		foreach(BS_ACP_Menu::get_instance()->get_menu_items() as $group)
		{
			$categories[] = array(
				'name' => $locale->lang($group['title']),
				'mods' => array()
			);

			foreach($group['modules'] as $mod => $data)
			{
				// skip items that have not default access
				if(isset($data['access']) && $data['access'] != 'default')
					continue;
				
				// are the permissions?
				$has_direct_access = $acpaccess->element_exists_with(array(
					'module' => $mod,
					'access_type' => $atype,
					'access_value' => $aval
				));
				if($type == 'user')
				{
					$access = $has_direct_access;
					// he/she don't has direct access, so we check if any of the usergroups
					// the user belongs to has access
					if(!$access)
					{
						foreach($usergroups as $gid)
						{
							$check = $acpaccess->element_exists_with(array(
								'module' => $mod,
								'access_type' => 'group',
								'access_value' => $gid
							));
							if($check)
							{
								$access = true;
								break;
							}
						}
					}

					$has_access = BS_ACP_Utils::get_instance()->get_yesno($access);
				}
				else
					$has_access = BS_ACP_Utils::get_instance()->get_yesno($has_direct_access);

				$categories[count($categories) - 1]['mods'][] = array(
					'name' => $locale->lang($data['title']),
					'current_permission' => $has_access,
					'module' => $mod,
					'has_direct_access' => $has_direct_access
				);
			}
		}
		
		$tpl->add_array('categories',$categories);
	}
	
	/**
	 * Determines the username to use
	 *
	 * @return string the username
	 */
	private function _get_username()
	{
		$input = PLIB_Props::get()->input();

		$username = $input->get_var('user_name','post',PLIB_Input::STRING);
		if($username == null)
			$username = $input->get_var('name','get',PLIB_Input::STRING);
		return $username;
	}
	
	/**
	 * Determines the group to use
	 *
	 * @return int the group-id
	 */
	private function _get_group()
	{
		$input = PLIB_Props::get()->input();

		$group = $input->get_var('user_group','post',PLIB_Input::ID);
		if($group == null)
			$group = $input->get_var('group','get',PLIB_Input::ID);
		return $group;
	}
}
?>